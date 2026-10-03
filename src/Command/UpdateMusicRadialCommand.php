<?php

namespace App\Command;

use App\Helper\ApiRequester;
use App\Helper\VideoHelper;
use App\Repository\RadialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsCommand(
    name: 'shufler:update-music-radial',
    description: 'Search key for radial tracks',
)]
class UpdateMusicRadialCommand extends Command
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RadialRepository       $radialRepository,
        private readonly ApiRequester           $apiRequester,
        ?string                                 $name = null
    ) {
        parent::__construct($name);
    }

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
        $io = new SymfonyStyle($input, $output);
        $tracks  = $this->radialRepository
            ->createQueryBuilder('r')
            ->orderBy('r.year', \SortDirection::Descending)
            ->andWhere("r.youtubeKey IS NULL")
            ->setMaxResults(200)
            ->getQuery()->getResult();

        $i = $nbNope = 0;
        $message = '';
        foreach ($tracks as $track) {
            try {
                $search = $track->getAuthor() . ' ' . $track->getName();
                $response = $this->apiRequester->sendRequest(VideoHelper::YOUTUBE,'/search', [
                    'q' => $search,
                ]);

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $resultYouTube = json_decode($response->getContent(), true)['items'] ?? [];
                    if (!empty($resultYouTube[0]['id']['videoId'])) {
                        $track->setYoutubeKey($resultYouTube[0]['id']['videoId']);
                    } else {
                        $track->setYoutubeKey('nope');
                        $nbNope++;
                    }

                    $i++;
                } elseif ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                    $track->setYoutubeKey('nope');
                    $nbNope++;
                } else {
                    $message = sprintf('No more request : %s %s', $track->getAuthor(), $track->getName());
                    break;
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
                break;
            }
        }
        $this->entityManager->flush();

        $now = new \DateTime();
        $io->writeln(sprintf('%s: %d tracks updated, %d ignorés', $now->format('d-m-Y G:i:s'), $i, $nbNope));

        return Command::SUCCESS;
    }
}
