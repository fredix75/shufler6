<?php

namespace App\Command;

use App\Enum\FileTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'shufler:import-data',
    description: 'Data Import command',
)]
class DataImportCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private SerializerInterface $serializer,#[Autowire('%import_directory%')] private string $dir)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('filename', InputArgument::REQUIRED, 'Le nom du fichier')
            ->addArgument('entity', InputArgument::REQUIRED, 'L\'entité à mapper')
            //->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fileName = $input->getArgument('filename');
        $filePath = $this->dir . '/'.$fileName;
        $entityClass = $input->getArgument('entity');

        if(!\file_exists($filePath)) {
            $io->error('No file existing, DUDE !');
            return Command::FAILURE;
        }

        if (!\in_array($extension = pathinfo($filePath, PATHINFO_EXTENSION), array_column(FileTypeEnum::cases(), 'value'))) {
            $io->error('Filetype non available. Déso, repasse quand tu veux');
            return Command::FAILURE;
        }

        $entityClass = 'App\\Entity\\'.$entityClass;

        if (!class_exists($entityClass)) {
            $io->error('Entity non available. Tu peux pas');
        }

        if ($extension == FileTypeEnum::JSON) {
            $n = 0;
            foreach (json_decode(file_get_contents($filePath)) as $i => $data) {
                $data = $this->serializer->serialize($data, 'json');
                $object = $this->serializer->deserialize($data, $entityClass, 'json');
                $this->em->persist($object);
                $n++;
                if ($i % 500 === 0) {
                    $this->em->flush();
                }
            }

            $this->em->flush();
        }

        $io->success(sprintf('Import Operation succeed: %d lines imported as %s', $n, $entityClass));

        return Command::SUCCESS;
    }
}
