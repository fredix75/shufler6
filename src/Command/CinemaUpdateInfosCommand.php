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
    name: 'cinema:update-infos',
    description: 'Add a short description for your command',
)]
class CinemaUpdateInfosCommand extends Command
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

        $movies = $this->em->getRepository(Film::class)->findBy(['verified' => false, 'noRef' => false, 'type' => $type], []);

        foreach ($movies as $i => $movie) {
            try {
                $response = $this->apiRequester->sendRequest('tmdb', '/movie/'.$movie->getTmdbId());

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $response = json_decode($response->getContent(), true) ?? [];

                    if (!empty($response)) {
                        $prods = array_map(function($item){
                            return $item['id'];
                        }, $response['production_companies']);
                        $collection = !empty($response['belongs_to_collection']) ? $response['belongs_to_collection']['id'] : null;
                        if (!empty($response['release_date'])) {
                            $movie->setDate(new \DateTime($response['release_date']));
                        }

                        $movie->setOverview($response['overview']);
                        $movie->setCountry($response['origin_country']);
                        $movie->setProduction($prods);
                        $movie->setBelongsToCollection($collection);
                        $movie->setVerified(true);
                        $this->em->persist($movie);
                    }
                } else {
                    $io->warning(sprintf('Pas de résultat pour %s', $movie->getName()));
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
