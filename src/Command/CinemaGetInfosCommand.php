<?php

namespace App\Command;

use App\Entity\Film;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'cinema:get-infos',
    description: 'Add a short description for your command',
)]
class CinemaGetInfosCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, #[Autowire('%kernel.project_dir%')] private string $dir)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dir = scandir($this->dir . '/public/uploads/cinema');

        foreach($dir as $i => $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $nameWithoutExtension = pathinfo($file, PATHINFO_FILENAME);
            $film = new Film();
            $film->setName(ucfirst(strtolower($nameWithoutExtension)));
            $film->setPicture($file);
            $this->em->persist($film);
            if ($i%500 === 0) {
                $this->em->flush();
            }
        }
        $this->em->flush();

        $io->success('Nickel');

        return Command::SUCCESS;
    }
}
