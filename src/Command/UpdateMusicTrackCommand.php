<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Response;

#[AsCommand(
    name: 'shufler:update-music-track',
    description: 'Add a short description for your command',
)]
class UpdateMusicTrackCommand extends ImportTracksCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tracks  = $this->trackRepository
            ->createQueryBuilder('t')
            ->orderBy('t.titre', 'ASC')
            ->addOrderBy('t.auteur', 'ASC')
            ->addOrderBy('t.numero', 'ASC')
            ->addOrderBy('t.album', 'ASC')
            ->andWhere("t.youtubeKey IS NULL")
            ->setMaxResults(200)
            ->getQuery()->getResult();

        $i = 0;
        foreach ($tracks as $track) {
            try {
                $search = $track->getAuteur() . ' ' . $track->getTitre();
                $response = $this->requestTrack($search, 'video');

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $resultYouTube = json_decode($response->getContent(), true)['items'] ?? [];
                    $track->setYoutubeKey($resultYouTube[0]['id']['videoId'] ?? 'nope');
                } elseif ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                    $track->setYoutubeKey('nope');
                }

                $this->entityManager->persist($track);
                $i++;

            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                break;
            }
        }

        $this->entityManager->flush();

        $io->success('Process fini: ' . $i . ' updated tracks.');

        return Command::SUCCESS;
    }
}
