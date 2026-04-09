<?php

namespace App\Command;

use App\Entity\Genrefilm;
use App\Helper\ApiRequester;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Response;

#[AsCommand(
    name: 'cinema:get-genres',
    description: 'Add a short description for your command',
)]
class CinemaGetGenresCommand extends Command
{
    public function __construct(private ApiRequester $apiRequester, private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $response = $this->apiRequester->sendRequest('tmdb', '/genre/movie/list', [
                'language' => 'fr',
            ]);

        } catch(\Exception $e) {
            $io->error('ERREUR API');
        }

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $response = json_decode($response->getContent(), true) ?? [];
            if (!empty($response['genres'])) {
                foreach($response['genres'] as $r) {
                    $genre = new Genrefilm();
                    $genre->setTmdbId($r['id']);
                    $genre->setName($r['name']);
                    $this->em->persist($genre);
                }
                $this->em->flush();
            } else {
                $io->warning('Pas de résultat pour cette langue');
            }
        }

        $io->success('Genres OK');

        return Command::SUCCESS;
    }
}
