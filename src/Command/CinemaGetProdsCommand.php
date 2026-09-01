<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Film;
use App\Entity\CinemaProd;
use App\Helper\ApiRequester;

#[AsCommand(
    name: 'cinema:get-prods',
    description: 'Add a short description for your command',
)]
class CinemaGetProdsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private ApiRequester $apiRequester)
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
		
		$productions = $this->em->getRepository(Film::class)->findDistinctProds();
		$section = $output->section();
		
		foreach ($productions as $i => $prod) {
			try {
                $response = $this->apiRequester->sendRequest('tmdb', '/company/'.$prod[0]);

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $response = json_decode($response->getContent(), true) ?? [];

					$section->overwrite(sprintf('%d%% : %s', round(100 * ($i + 1) / count($productions), 0), $response['name']));
                    if (!empty($response)) {
						$cinemaProd = new CinemaProd();
						$cinemaProd->setName($response['name']);
						$cinemaProd->setDescription($response['description']);
						$cinemaProd->setHeadquarters($response['headquarters']);
						$cinemaProd->setLogo($response['logo_path']);
						$cinemaProd->setHomePage($response['homepage']);
						$cinemaProd->setTmdbId($response['id']);
						$cinemaProd->setCountry($response['origin_country']);
						$cinemaProd->setParentCompany($response['parent_company']);
                        $this->em->persist($cinemaProd);
                    }
                } else {
                    $io->warning(sprintf('Pas de résultat pour prod #%d', $prod[0]()));
                }
				
				if ($i%100 === 0) {
					$this->em->flush();
				}

            } catch (\Exception $e) {
                $io->error(sprintf('ERREUR pour %d : %s', $prod[0], $e->getMessage()));
            }
		}
		
		$this->em->flush();
		
        $io->success('Great job man!');

        return Command::SUCCESS;
    }
}
