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
use App\Entity\Commune;
use App\Helper\ApiRequester;


#[AsCommand(
    name: 'geo:get-communes',
    description: 'Add a short description for your command',
)]
class GeoGetCommunesFranceCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private ApiRequester $apiRequester)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
		
		$io->writeln('Import des communes de France');
		
		$response = $this->apiRequester->sendRequest('gouv', '/communes?fields=nom,code,codeDepartement,siren,codeEpci,codeRegion,codesPostaux,population,centre,zone,surface');
		
		$section = $output->section();

		if ($response->getStatusCode() === Response::HTTP_OK) {
			$result = json_decode($response->getContent(), true) ?? [];
			foreach ($result as $n => $arrayCommune) {
				
				$section->overwrite(sprintf("<fg=#FF57CA;options=bold>%.2f %% - %d / %d</> - dept %s - %s", round(100*($n+1)/count($result), 2), $n+1, count($result), 
					$arrayCommune['codeDepartement'], $arrayCommune['nom']));
				
				if (count($arrayCommune['codesPostaux']) > 1) {
					$response2 = $this->apiRequester->sendRequest('gouv','/communes?nom='.$arrayCommune['nom'].'&type=arrondissement-municipal&fields=nom,code,codeDepartement,siren,codeEpci,codeRegion,codesPostaux,population,centre,zone,surface');
					if ($response2->getStatusCode() === Response::HTTP_OK) {
						$result2 = json_decode($response2->getContent(), true) ?? [];
						if (!empty($result2)) {
							foreach ($result2 as $m => $arrayArrdt) {
								$commune = $this->hydrateCommune($arrayArrdt);
								$this->em->persist($commune);
							}							
						} else {
							foreach ($arrayCommune['codesPostaux'] as $m => $cp) {
								$arrayCommune2 = $arrayCommune;
								$arrayCommune2['codesPostaux'] = [];
								$arrayCommune2['codesPostaux'][0] = $cp;
								$arrayCommune2['population'] = $m === 0 ? $arrayCommune2['population'] : 0;
								$arrayCommune2['surface'] = $m === 0 ? $arrayCommune2['surface'] : 0;
								$arrayCommune2['centre']['coordinates'] = $m === 0 ? $arrayCommune2['centre']['coordinates'] : [];
								$commune = $this->hydrateCommune($arrayCommune2);
								$this->em->persist($commune);
							}							
						}

						$this->em->flush();
					}
					
					continue;
				}
				
				$commune = $this->hydrateCommune($arrayCommune);

				$this->em->persist($commune);

				if ($n%100 === 0) {
					$this->em->flush();
				}
			}
			
			$this->em->flush();
		}					

        $io->success('Communes importées');

        return Command::SUCCESS;
    }
	
	private function hydrateCommune(array $arrayCommune): Commune
	{
		$commune = new Commune();
		$commune->setNom($arrayCommune['nom']);
		$commune->setCode($arrayCommune['code']);
		$commune->setDepartementCode($arrayCommune['codeDepartement']);
		$commune->setSiren($arrayCommune['siren'] ?? '');
		$commune->setEpci($arrayCommune['codeEpci'] ?? '');
		$commune->setRegionCode($arrayCommune['codeRegion']);					
		$commune->setCp(!empty($arrayCommune['codesPostaux']) ? $arrayCommune['codesPostaux'][0] : '');
		$commune->setCoord($arrayCommune['centre']['coordinates']);
		$commune->setZone($arrayCommune['zone']);
		$commune->setSurface($arrayCommune['surface']);
		$commune->setPopulation(!empty($arrayCommune['population']) ? $arrayCommune['population'] : 0);
		
		return $commune;
	}
}
