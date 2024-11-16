<?php

namespace App\Command;

use App\Helper\VideoHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsCommand(
    name: 'shufler:update-music-album',
    description: 'Search key for playlists',
)]
class UpdateMusicAlbumCommand extends ImportTracksCommand
{

    /**
     * @throws RedirectionExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws SyntaxError
     * @throws ServerExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $albums  = $this->albumRepository
            ->createQueryBuilder('a')
            ->orderBy('a.name', 'ASC')
            ->addOrderBy('a.auteur', 'ASC')
            ->andWhere("a.youtubeKey IS NULL")
            ->setMaxResults(200)
            ->getQuery()->getResult();

        $i = 0;
        foreach ($albums as $album) {

            if (mb_stripos($album->getName(), 'divers') !== false) {
                $album->setYoutubeKey('nope');
                continue;
            }

            // Youtube Links
            try {
                $search = (strtolower($album->getAuteur()) === 'divers' ? '' : $album->getAuteur().' ') . $album->getName();
                $response = $this->apiRequester->sendRequest(VideoHelper::YOUTUBE,'/search', [
                    'q'     => $search,
                    'type'  => 'playlist',
                ]);

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $resultYouTube = json_decode($response->getContent(), true)['items'] ?? [];
                    if (!empty($resultYouTube[0]['id']['playlistId'])) {
                        $album->setYoutubeKey($resultYouTube[0]['id']['playlistId']);
                    } else {
                        $album->setYoutubeKey('nope');
                    }

                    $i++;
                } elseif ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                    $album->setYoutubeKey('nope');
                } else {
                    $message = sprintf('No more request : %s %s', $album->getAuteur(), $album->getName());
                    break;
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
                break;
            }

            // Pictures
            if (str_contains($album->getPicture(), 'no_cover') && strtolower($album->getName()) !== 'divers') {
                try {
                    $response = $this->apiRequester->sendRequest('last_fm', '', [
                        'artist'   => strtolower($album->getAuteur()) === 'divers' ? 'Various Artists' : $album->getAuteur() ,
                        'album'   => $album->getName(),
                        'method' => 'album.getInfo',
                    ]);
                    if ($response->getStatusCode() === Response::HTTP_OK) {
                        $response = json_decode($response->getContent(), true) ?? [];
                        if (!empty($response['album'])) {
                            $album->setPicture($response['album']['image'][4]['#text'] ?? '');
                        }
                    }
                } catch(\Exception $e) {
                    // pas grave : dans ce cas, on continue sans problème
                }
            }
        }

        $this->entityManager->flush();

        $html = $this->twig->render('api/updateAlbums.html.twig', [
            'message' => $message ?? '',
            'nb' => $i
        ]);

        $output->writeln($html);

        return Command::SUCCESS;
    }
}
