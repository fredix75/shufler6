<?php

namespace App\Command;

use App\Helper\ApiRequester;
use App\Helper\VideoHelper;
use App\Repository\MusicCollection\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'shufler:clean-videos',
    description: 'Check if videos are really  availables',
)]
class CleanVideosCommand extends Command
{

    public function __construct(private TrackRepository $trackRepository, private EntityManagerInterface $entityManager, private ApiRequester $apiRequester, string $name = null)
    {

        parent::__construct($name);
    }

    /**
     * @throws ORMException
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws OptimisticLockException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln('<comment>Step 1. Loading Tracks</comment>');

        $tracks = $this->trackRepository->createQueryBuilder('t')
            ->orderBy('t.titre', 'ASC')
            ->addOrderBy('t.auteur', 'ASC')
            ->addOrderBy('t.numero', 'ASC')
            ->addOrderBy('t.album', 'ASC')
            ->andWhere("t.youtubeKey IS NOT NULL")
            ->andWhere("t.isCheck = FALSE")
            ->getQuery()->getResult();

        $size = count($tracks);

        if ($size === 0) {
            $this->trackRepository->resetChecks();
            $io->success('Ok, Base Track is ready to be checked.');
            return Command::SUCCESS;
        }

        $io->writeln('<comment>Step 2. Checking Tracks</comment>');

        $progress = new ProgressBar($output, $size);
        $progress->start();

        $i = $n = 0;
        foreach ($tracks as $track) {
            if (empty($track->getYoutubeKey())) {
                continue;
            }

            $response = $this->apiRequester->sendRequest(VideoHelper::YOUTUBE, '/videos', [
                'part' => 'id',
                'id' => $track->getYoutubeKey(),
            ]);

            if ($response->getStatusCode() === Response::HTTP_FORBIDDEN) {
                break;
            }

            $response = json_decode($response->getContent(), true);
            if (empty($response['items'])) {
                $track->setYoutubeKey(null);
                dump('removed,');
                $n++;
            } else {
                $track->setCheck(true);
                dump('checked');
            }

            $this->entityManager->persist($track);
            $i++;
            if ($i%50 === 0) {
                $progress->advance(50);
                $this->entityManager->flush();
            }

            if ($i>20) {
                break;
            }
        }

        $this->entityManager->flush();
        $progress->finish();
        $io->success(sprintf('Operation finished. %d Tracks checked. %d Tracks removed.', $i, $n));

        return Command::SUCCESS;
    }
}
