<?php

namespace App\Command;

use App\Entity\Film;
use App\Helper\ApiRequester;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

#[AsCommand(
    name: 'cinema:get-infos',
    description: 'Add a short description for your command',
)]
class CinemaGetInfosCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private ApiRequester $apiRequester, #[Autowire('%kernel.project_dir%')] private string $dir)
    {
        parent::__construct();
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $movies = $this->em->getRepository(Film::class)->findBy(['year' => null, 'verified' => false], [], 1000);

        foreach ($movies as $i => $movie) {
            $io->writeln($movie->getName());
            $offset = 0;
            while (true) {
                try {
                    $response = $this->apiRequester->sendRequest('tmdb', '/search/movie', [
                        'query' => $movie->getName(),
                    ]);

                    if ($response->getStatusCode() === Response::HTTP_OK) {
                        $response = json_decode($response->getContent(), true) ?? [];
                        if (!empty($response['release_date']) && (new \DateTime($response['release_date']))->format('Y') > 2002) {
                            $offset++;
                            continue;
                        }
                        if (!empty($response['results'])) {
                            $response = $response['results'][$offset];

                            $movie->setYear(!empty($response['release_date']) ? (new \DateTime($response['release_date']))->format('Y') : null);
                            $movie->setOverview($response['overview']);
                            $movie->setOriginalTitle($response['original_title']);
                            $movie->setOriginalLanguage($response['original_language']);
                            $movie->setTmdbId($response['id']);
                            $movie->setPosterPath($response['poster_path']);
                            $movie->setBackdropPath($response['backdrop_path']);
                            $movie->setPopularity($response['popularity']);
                            $movie->setGenres($response['genre_ids']);
                            $this->em->persist($movie);
                        } else {
                            $io->warning(sprintf('Pas de résultat pour %s', $movie->getName()));
                        }
                    }
                } catch (\Exception $e) {
                    $io->error(sprintf('ERREUR pour %s', $movie->getName()));
                }
                break;
            }

            if ($i%100 === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();

        $io->success('Nickel');

        return Command::SUCCESS;
    }
}
