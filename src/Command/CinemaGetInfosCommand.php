<?php

namespace App\Command;

use App\Entity\Film;
use App\Enum\FilmTypeEnum;
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
    public function __construct(private EntityManagerInterface $em, private ApiRequester $apiRequester)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // ...
            ->addArgument('type', InputArgument::REQUIRED, 'Type of the media')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!\in_array($type = $input->getArgument('type'), array_column(FilmTypeEnum::cases(), 'value'))) {
            $io->error('BAD ARGUMENT');
            return Command::FAILURE;
        }

        $seuilDate = $type === FilmTypeEnum::FILM ? 2002 : null;
        $movies = $this->em->getRepository(Film::class)->findBy(['verified' => false, 'type' => $type], []);

        foreach ($movies as $i => $movie) {
            $io->writeln($movie->getName());
            $offset = 0;

            try {
                $response = $this->apiRequester->sendRequest('tmdb', '/search/movie', [
                    'query' => $movie->getName(),
                ]);

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $result = json_decode($response->getContent(), true) ?? [];
                    if (!empty($result['results'])) {
                        while (true) {
                            if (empty($result['results'][$offset])) {
                                $io->info(sprintf('pas de résultat pertinent pour %s', $movie->getName()));
                                break;
                            }
                            $response = $result['results'][$offset];
                            if ($seuilDate && !empty($response['release_date']) && (new \DateTime($response['release_date']))->format('Y') > $seuilDate) {
                                $offset++;
                                continue;
                            }

                            $movie->setYear(!empty($response['release_date']) ? (new \DateTime($response['release_date']))->format('Y') : null);
                            $movie->setDate(!empty($response['release_date']) ? new \DateTime($response['release_date']) : null);
                            $movie->setOverview($response['overview']);
                            $movie->setOriginalTitle($response['original_title']);
                            $movie->setOriginalLanguage($response['original_language']);
                            $movie->setTmdbId($response['id']);
                            $movie->setPosterPath($response['poster_path']);
                            $movie->setBackdropPath($response['backdrop_path']);
                            $movie->setPopularity($response['popularity']);
                            $movie->setGenres($response['genre_ids']);
                            $this->em->persist($movie);
                            break;
                        }
                    } else {
                        $io->warning(sprintf('Pas de résultat pour %s', $movie->getName()));
                    }
                }
            } catch (\Exception $e) {
                $io->error(sprintf('ERREUR pour %s', $movie->getName()));
            }

            if ($i % 100 === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();

        $io->success('Nickel');

        return Command::SUCCESS;
    }
}
