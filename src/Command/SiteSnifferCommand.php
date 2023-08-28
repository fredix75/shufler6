<?php

namespace App\Command;

use App\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'shufler:site-sniffer',
    description: 'Sniff Pictures from Url',
)]
class SiteSnifferCommand extends Command
{
    protected FileHelper $fileHelper;
    protected string $uploadDir;

    public function __construct(
        FileHelper $fileHelper,
        ParameterBagInterface $parameterBag,
        string $name = null
    )
    {
        $this->fileHelper = $fileHelper;
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

        $dirName = preg_replace(['/\.[\.]+/', '/[^\w\s\.\-\']/'], ['.', ''], $dirName);

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
                try {
                    $fileName = sprintf(
                        "%s_%s",
                        sprintf("%05d", $index),
                        sprintf("%04d", $key)
                    );
                    $this->fileHelper->copyFileFromUrl($val[1], sprintf('%s/%s', $this->uploadDir, $dirName), $fileName, $url);
                } catch (\Exception $e) {
                    $io->warning(sprintf('Impossible to copy this file : %s%s', $url, $val[1]));
                    $io->warning($e->getMessage());
                }

                $section->write('-');
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
