<?php

namespace App\Command;

use App\Entity\PictureCollection\Painter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'frixture:get-year',
    description: 'Add a short description for your command',
)]
class FrixtureGetYearCommand extends Command
{
    public function __construct(private EntityManagerInterface $manager)
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $painters = $this->manager->getRepository(Painter::class)->findAll();
        //$painters = [$this->manager->getRepository(Painter::class)->find(74)];

        $io = new SymfonyStyle($input, $output);

        foreach($painters as $painter) {
            $bio = $painter->getBio();
            $ok = preg_match_all('/\b(1\d{3}|20[0-4]\d|2050)\b/', $bio, $matches);

            if ($ok && count($matches[0]) > 1) {
                $birthDate = (int)$matches[0][0];
                $deathDate = (int)$matches[0][1];
                $altDate = !empty($matches[0][2]) ? (int)$matches[0][2] : 0;

                if ($deathDate - $birthDate < 18) {
                    $io->note(sprintf('Artist #%d : %s strange date : %d - new date : %d ', $painter->getId(), $painter->getName(), $deathDate, $altDate));
                    $deathDate = $altDate;
                }
                $painter->setBirthYear($birthDate);
                $painter->setDeathYear($deathDate);
            } else {
                print($ok);
            }
        }

        $this->manager->flush();

        $io->success('OK CIAO');

        return Command::SUCCESS;
    }
}
