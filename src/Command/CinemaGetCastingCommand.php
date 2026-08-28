<?php

namespace App\Command;

use App\Entity\CinemaPeople;
use App\Entity\Film;
use App\Entity\FilmCasting;
use App\Entity\PictureFilm;
use App\Enum\FilmTypeEnum;
use App\Helper\ApiRequester;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Response;

#[AsCommand(
    name: 'cinema:get-casting',
    description: 'Add a short description for your command',
)]
class CinemaGetCastingCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly ApiRequester $apiRequester)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);


        //$movies = $this->em->getRepository(Film::class)->findBy(['type' => 'FILM', 'verified' => false, 'noRef' => false], [], 1);
        $movies = [$this->em->getRepository(Film::class)->find(8055)];

        foreach ($movies as $i => $movie) {
            $nbDirs = $nbActors = 0;
            $io->writeln('<fg=#FF57CA;options=bold>' . $movie->getName() . '</>');

            try {
                $response = $this->apiRequester->sendRequest('tmdb', '/movie/' . $movie->getTmdbId() . '/credits');

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $result = json_decode($response->getContent(), true) ?? [];

                    //dd($result);

                    foreach ($result as $k => $team) {
                        if (\in_array($k, ['cast', 'crew'])) {
                            foreach ($team as $actor) {
                                if (!($k === 'cast' && !empty($actor['character']) || $k === 'crew' && $actor['job'] === 'Director')) {
                                    continue;
                                }

                                $people = $this->em->getRepository(CinemaPeople::class)->findOneBy(['tmdbId' => $actor['id']]);

                                if (!$people) {
                                    $response = $this->apiRequester->sendRequest('tmdb', '/person/' . $actor['id']);
                                    if ($response->getStatusCode() === Response::HTTP_OK) {
                                        $result = json_decode($response->getContent(), true) ?? [];

                                        $people = new CinemaPeople();
                                        $people->setName($result['name']);
                                        $people->setBio($result['biography']);
                                        $people->setBirthDate(new \DateTime($result['birthday']));
                                        if (!empty($result['deathday'])) {
                                            $people->setDeathDate(new \DateTime($result['deathday']));
                                        }
                                        $people->setBirthPlace($result['place_of_birth']);
                                        $people->setPopularity($result['popularity']);
                                        $people->setPicture($result['profile_path']);
                                        $people->setTmdbId($result['id']);
                                        if ($result['known_for_department'] === 'Acting') {
                                            $job = 'ACTOR';
                                        } else if ($result['known_for_department'] === 'Directing') {
                                            $job = 'DIRECTOR';
                                        }
                                        $people->setJob($job);

                                        $this->em->persist($people);
                                        $this->em->flush();
                                    }
                                }
                                $casting = new FilmCasting();
                                $casting->setCinemaPeople($people);
                                $casting->setRole($actor['character'] ?? '');
                                if ($k === 'cast') {
                                    $job = 'ACTOR';
                                    $nbActors++;
                                } else if ($k === 'crew' && $actor['job'] === 'Director') {
                                    $job = 'DIRECTOR';
                                    $nbDirs++;
                                }
                                $casting->setJob($job);
                                $casting->setFilm($movie);

                                $this->em->persist($casting);
                            }
                        }
                    }
                } else {
                    $io->warning('BAD RESPONSE: ' . $response->getStatusCode());
                }
            } catch (\Exception $e) {
                $io->error(sprintf('ERREUR pour %s : %s', $movie->getName(), $e->getMessage()));
            }

            $movie->setVerified(true);
            $io->writeln(sprintf("    %d directors - %d actors", $nbDirs, $nbActors));
            if ($i % 10 === 0) {
                $this->em->flush();
            }
        }
        $this->em->flush();
        $io->success('bien gros, wesh');

        return Command::SUCCESS;
    }
}
