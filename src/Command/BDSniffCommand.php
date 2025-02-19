<?php

namespace App\Command;

use DateTime;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'shufler:bd-sniff',
    description: 'Sniff sniff',
)]
class BDSniffCommand extends Command
{
    private Filesystem $filesystem;

    private string $directory;

    public function __construct(Filesystem $filesystem, ParameterBagInterface $parameterBag, ?string $name = null) {
        $this->filesystem = $filesystem;
        $this->directory = $parameterBag->get('uploads').'sniffer/Revues/';
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('revue', InputArgument::REQUIRED, 'Nom de la Revue')
            ->addArgument('pattern', InputArgument::REQUIRED, 'Pattern')
            ->addArgument('limite', InputArgument::REQUIRED, 'Limite')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $revue = $input->getArgument('revue');
        $pattern = $input->getArgument('pattern');
        $limit = $input->getArgument('limite');

        $url = 'URL root';

        $browser = new HttpBrowser(HttpClient::create());

        $section = $output->section();

        while (@file_get_contents($url.$pattern)) {
            preg_match_all('/__[0-9]/', $pattern, $match);
            $index = preg_replace('/[^0-9]+/', '', $match[count($match)-1])[0] ?? 0;

            $section->writeln($pattern);
            $crawler = $browser->request('GET', $url.$pattern);
            $li = $crawler->filter('.liste-revues')->children();

            foreach ($li as $l) {
                $txt = $l->textContent;
                preg_match('/#[0-9]+/', $txt, $matches);
                $a = preg_replace('/[^0-9]+/', '', $matches);
                $numero = $a[0] ?? "HS";

                preg_match('/Identifiant :[0-9]+/', $txt, $matches);
                $b = preg_replace('/[^0-9]+/', '', $matches);
                $id = $b[0];

                preg_match('/Parution :\d+\/\d+\/\d+/', $txt, $matches);
                $c = preg_replace('/[^(\d+\/\d+\/\d+)]/', '', $matches);
                if (!empty($c[0])) {
                    $dt = DateTime::createFromFormat("d/m/Y", $c[0]);
                    $date = $dt->format('Y-m-d');
                } else {
                    $date = '____-__-__';
                }

                if ($this->filesystem->exists($this->directory . 'Revue_' . $id . '.jpg') && !$this->filesystem->exists($this->directory . $revue.'_' . $numero . '_' . $date . '.jpg')) {
                    $this->filesystem->rename($this->directory . 'Revue_' . $id . '.jpg', $this->directory . $revue.'_' . $numero . '_' . $date . '.jpg');
                }

      //          $this->filesystem->rename($this->directory . $revue.'_0' . $numero . '_' . $date . '.jpg', $this->directory . $revue.'__' . $numero . '_' . $date . '.jpg');

                $section->writeln($revue.'_' . $numero . '_' . $date . '.jpg');

            }

            $index++;
            if ($index >= $limit) {
                break;
            }
            $pattern= preg_replace( '~(.*)[0-9]+~su', '${1}'.$index, $pattern);
            sleep(3);
        }


        $io->success('On a bien snifffffffféééééé.');

        return Command::SUCCESS;
    }
}
