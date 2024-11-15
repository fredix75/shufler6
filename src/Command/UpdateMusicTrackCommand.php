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
    name: 'shufler:update-music-track',
    description: 'Search key for tracks',
)]
class UpdateMusicTrackCommand extends ImportTracksCommand
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
        $tracks  = $this->trackRepository
            ->createQueryBuilder('t')
            ->orderBy('t.titre', 'ASC')
            ->addOrderBy('t.auteur', 'ASC')
            ->addOrderBy('t.album', 'ASC')
            ->addOrderBy('t.numero', 'ASC')
            ->andWhere("t.youtubeKey IS NULL")
            ->setMaxResults(200)
            ->getQuery()->getResult();

        $i = 0;
        foreach ($tracks as $track) {
            try {
                $search = $track->getAuteur() . ' ' . $track->getTitre();
                $response = $this->apiRequester->sendRequest(VideoHelper::YOUTUBE,'/search', [
                    'q' => $search,
                ]);

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $resultYouTube = json_decode($response->getContent(), true)['items'] ?? [];
                    if (!empty($resultYouTube[0]['id']['videoId'])) {
                        $track->setYoutubeKey($resultYouTube[0]['id']['videoId']);
                        $track->setIsCheck(true);
                    } else {
                        $track->setYoutubeKey('nope');
                    }

                    $i++;
                } elseif ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                    $track->setYoutubeKey('nope');
                } else {
                    $message = sprintf('No more request : %s %s', $track->getAuteur(), $track->getTitre());
                    break;
                }

                $this->entityManager->persist($track);

            } catch (\Exception $e) {
                $message = $e->getMessage();
                break;
            }
        }

        $this->entityManager->flush();

        $html = $this->twig->render('api/updateTracks.html.twig', [
            'message' => $message,
            'nb' => $i
        ]);

        $output->writeln($html);

        return Command::SUCCESS;
    }
}
