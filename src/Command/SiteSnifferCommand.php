<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(
    name: 'shufler:site-sniffer',
    description: 'Sniff Pictures from Url',
)]
class SiteSnifferCommand extends Command
{
    protected string $uploadDir;

    public function __construct(
        ParameterBagInterface $parameterBag,
        string $name = null
    )
    {
        $this->uploadDir = $parameterBag->get('sniffer')['upload_directory'];
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'Website URL')
            ->addArgument('name', InputArgument::REQUIRED, 'Directory Name')
            ->addArgument('serial_pattern', InputArgument::OPTIONAL, 'Serial Pattern e.g: page1.html')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');
        $dirName = $input->getArgument('name');
        $pattern = $input->getArgument('serial_pattern') ?? null;

        $pieces = parse_url($url);
        $rootUrl = $pieces['scheme'].'://'.$pieces['host'];

        $filesystem = new Filesystem();

        $dirName = preg_replace(array('/\.[\.]+/', '/[^\w\s\.\-\']/'), array('.', ''), $dirName);
        $uploadDirName = $this->uploadDir.'/'.$dirName;

        $finished = false;
        if (empty(@get_headers($url.$pattern, 1))) {
            $io->error(sprintf('Website is not available : %s%s', $url, $pattern));
            return Command::FAILURE;
        }

        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }
        $section = $output->section();
        while (($page = @file_get_contents($url.$pattern)) && !$finished) {
            $index = $pattern ? preg_replace( '/[^0-9]+/', '', $pattern) : 1;

            $section->overwrite('#'.$index);
            preg_match_all('/<img[^>]*'.'src=[\"|\'](.*[.]jpe?g)[\"|\']/Ui', $page, $matches, PREG_SET_ORDER);
            foreach ($matches as $key => $val) {
                // on enlève les "../" de la src pour l'ajouter à la racine de l'url pour atteindre l'image
                // TODO: à améliorer pour rendre compatible qq soit le path
                $imgUrl = $rootUrl.substr($val[1],2);
                $imgUrl = preg_replace('/\s/', '%20', $imgUrl);

                $filesystem->copy($imgUrl, $uploadDirName.'/tmp');
                $file = new File($uploadDirName.'/tmp');
                $filesystem->copy($uploadDirName.'/tmp', $uploadDirName.'/'.sprintf("%05d", $index).'_'.sprintf("%04d", $key).'.'.$file->guessExtension());
                unlink($file->getPathname());
            }

            $finished = true;
            if ($pattern) {
                $index++;
                $pattern= preg_replace( '/[0-9]+/', $index, $pattern);
                $finished = false;
            }
        }

        $io->success(sprintf('Sniffing has finished. The directory is : %s', $dirName));

        return Command::SUCCESS;
    }
}
