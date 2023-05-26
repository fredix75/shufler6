<?php

namespace App\Command;

use App\Entity\MusicCollection\Album;
use App\Entity\MusicCollection\Artist;
use App\Entity\MusicCollection\Track;
use App\Helper\CsvConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'shufler:import-music-collection',
    description: 'Import Music Collection from a CSV file',
)]
class ImportTracksCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private CsvConverter $converter;

    private HttpClientInterface $httpClient;

    private array $parameters;

    private string $apiUrl;

    private string $apiKey;

    private array $artistes = [];

    private array $albums = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        CsvConverter $csvConverter,
        HttpClientInterface $httpClient,
        array $parameters,
        string $apiUrl,
        string $apiKey
    ) {
        $this->entityManager = $entityManager;
        $this->converter = $csvConverter;
        $this->httpClient = $httpClient;
        $this->parameters = $parameters;
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();
        $io->writeln(sprintf('<comment>--- Start: %s ---</comment>', $now->format('d-m-Y G:i:s')));

        $output->writeln('<comment>Searchin\' File music.csv</comment>');

        $output->writeln('<comment>Step 1. Importing Tracks</comment>');

        if (!$this->importTracks($input, $output)) {
            $io->error('No Import : Check the File, Dude!');

            return Command::FAILURE;
        }

        $output->writeln('<comment>Step 2. Importing Artists</comment>');

        if (!$this->importArtists($input, $output)) {
            $io->error('No Import');

            return Command::FAILURE;
        }

        $output->writeln('<comment>Step 3. Importing Albums</comment>');

        if (!$this->importAlbums($input, $output)) {
            $io->error('No Import');

            return Command::FAILURE;
        }

        $now = new \DateTime();
        $io->writeln(sprintf('<comment>--- End: %s ---</comment>', $now->format('d-m-Y G:i:s')));

        $io->success('Process ending !');

        return Command::SUCCESS;
    }

    protected function importTracks(InputInterface $input, OutputInterface $output): bool
    {
        // Getting php array of data from CSV
        $data = $this->getDatas();

        if (empty($data)) {
            return false;
        }

        // TRUNCATE INITIAL DB -- désactivé pour le moment
        $query = 'TRUNCATE music_track;';


        // Define the size of record, the frequency for persisting the data and the current index of records
        $size = count($data);
        $i = 1;

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        header('Content-type: text/html; charset=UTF-8');
        // Processing on each row of data
        foreach ($data as $row) {
            $track = new Track();
            $track->setAuteur($row[0]);
            $track->setNumero($row[1]);
            $track->setTitre($row[2]);
            $row[3] = ((int)$row[3] * 1 == 0) ? '' : $row[3];
            $track->setAnnee((int)$row[3]);
            $track->setAlbum($row[4]);
            $track->setArtiste($row[5]);
            $track->setGenre($row[6]);
            $row[7] = (trim($row[7]) === 'Éditeur Inconnu') ? '' : $row[7];
            $track->setPays($row[7]);
            $track->setDuree($row[8]);
            $track->setBitrate($row[9]);
            $track->setNote((int)$row[10]);

            if ($track->getNote() == 5) {
                try {
                    $search = str_replace(' ', '%20', $row[0] . ' ' . $row[2]);
                    $response = $this->httpClient->request('GET', sprintf('%s/search', $this->apiUrl), [
                        'query' => [
                            'key'       => $this->apiKey,
                            'q'         => $search,
                            'part'      => 'snippet',
                            'maxResults'=> 5,
                        ],
                        'headers' => [
                            'Content-Type: application/json',
                            'Accept: application/json',
                        ]
                    ]);


                    $resultYouToube = json_decode($response->getContent(), true)['items'] ?? [];

                    // @todo à tester
                    //$track->setKey($resultYouToube['items'][0]['videoId']);
                } catch (\Exception $e) {

                }
            }

            $this->entityManager->persist($track);

            if (empty($this->artistes[$row[0]])) {
                $this->artistes[$row[0]] = 1;
            }

            if (empty($this->albums[$row[5]][$row[4]])) {
                $this->albums[$row[5]][$row[4]] = [
                    "genre" => $row[6],
                    "annee" => $row[3]
                ];
            }

            // Each 20 users persisted we flush everything
            if (($i%$this->parameters['batch_size']) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $progress->advance($this->parameters['batch_size']);
            }

            $i ++;
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $progress->finish();

        return true;
    }

    protected function importArtists(InputInterface $input, OutputInterface $output): bool
    {
        // TRUNCATE INITIAL DB
        $query = 'TRUNCATE artist;';

        // Define the size of record, the frequency for persisting the data and the current index of records
        $size = count($this->artistes);
        $i = 1;

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();
        foreach ($this->artistes as $artisteName => $value) {
            $artiste = new Artist();
            $artiste->setName($artisteName);
            try {
/*                $response = $this->httpClient->request('GET',  sprintf('%s', $this->getParameter('lastfm_api_url')), [
                    'query' => [
                        'api_key'  => $this->getParameter('youtube_key'),
                        'artist'   => $artisteName,
                        'method' => 'artist.getInfo',
                    ],
                    'headers' => [
                        'Content-Type: application/json',
                        'Accept: application/json',
                    ]
                ]);

                $artiste->setImageUrl($response->artist->image[4]->{'#text'});
                $artiste->setBio($response->artist->bio->content);*/
            } catch(\Exception $e) {

            }
            $this->entityManager->persist($artiste);

            // Each 20 users persisted we flush everything
            if (($i%$this->parameters['batch_size']) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $progress->advance($this->parameters['batch_size']);
            }

            $i ++;
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
        $progress->finish();

        return true;
    }

    protected function importAlbums(InputInterface $input, OutputInterface $output): bool
    {
        // TRUNCATE INITIAL DB
        $query = 'TRUNCATE album;';

        // Define the size of record, the frequency for persisting the data and the current index of records
        $size = count($this->albums);
        $i = 1;

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();
        foreach ($this->albums as $artisteName => $albums) {
            foreach ($albums as $albumName => $features) {
                $album = new Album();
                $album->setAuteur($artisteName);
                $album->setName($albumName);
                $album->setAnnee((int)$features['annee']);
                $album->setGenre($features['genre']);

                if (strtolower($albumName) != 'divers' && strtolower($artisteName) != 'divers') {
                    $search = $artisteName . " " . $albumName;
                    $search = str_replace(" ", "%20", $search);

                    try {
                        $response = $this->httpClient->request('GET', sprintf('%s/search', $this->apiUrl), [
                            'query' => [
                                'key'       => $this->apiKey,
                                'q'         => $search,
                                'part'      => 'snippet',
                                'type'      => 'playlist',
                                'maxResults'=> 1,
                            ],
                            'headers' => [
                                'Content-Type: application/json',
                                'Accept: application/json',
                            ]
                        ]);
                        $album->setYoutubeKey($response['items'][0]['playlistId']);
                    } catch (\Exception $e) {

                    }
                }

                $this->entityManager->persist($album);
                // Each 20 users persisted we flush everything
                if (($i%$this->parameters['batch_size']) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                    $progress->advance($this->parameters['batch_size']);
                }

                $i ++;
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
        $progress->finish();

        return true;
    }

    protected function getDatas(): array
    {
        $datas = $this->converter->setFilePath($this->parameters['csv_path'])->convert(false, ';');
        return $datas;
    }
}
