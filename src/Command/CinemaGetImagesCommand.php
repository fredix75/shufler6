<?php

namespace App\Command;

use App\Entity\Film;
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
    name: 'cinema:get-images',
    description: 'Add a short description for your command',
)]
class CinemaGetImagesCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly ApiRequester $apiRequester)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'Le type');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!\in_array($type = $input->getArgument('type'), array_column(FilmTypeEnum::cases(), 'value'))) {
            $io->error('BAD ARGUMENT');
            return Command::FAILURE;
        }

        $movies = $this->em->getRepository(Film::class)->findBy(['verified' => true, 'type' => $type], []);
        //$movies = [$this->em->getRepository(Film::class)->find(11950)];

        foreach ($movies as $movie) {
            $io->writeln($movie->getName());
            try {
                $response = $this->apiRequester->sendRequest('tmdb', '/movie/' . $movie->getTmdbId() . '/images');

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $result = json_decode($response->getContent(), true) ?? [];

                    foreach ($result as $k => $images) {
                        if (is_array($images)) {
                            foreach ($images as $img) {
                                $picture = new PictureFilm();
                                $picture->setFilm($movie);
                                $picture->setType($k);
                                $picture->setPath($img['file_path']);
                                $this->em->persist($picture);
                            }
                        }

                    }
                    $this->em->flush();
                }
            } catch (\Exception $e) {
                dd($e->getMessage());
                $io->error(sprintf('ERREUR pour %s', $movie->getName()));
            }

        }

        $io->success('bien gros, wesh');

        return Command::SUCCESS;
    }
}
