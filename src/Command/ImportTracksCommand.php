<?php

namespace App\Command;

use App\Entity\MusicCollection\Album;
use App\Entity\MusicCollection\Artist;
use App\Entity\MusicCollection\Track;
use App\Helper\CsvConverter;
use App\Repository\MusicCollection\ArtistRepository;
use App\Repository\MusicCollection\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use phpDocumentor\Reflection\DocBlock\Serializer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'shufler:import-music-collection',
    description: 'Import Music Collection from a CSV file',
)]
class ImportTracksCommand extends Command
{
    private array $parameters;

    private string $apiUrl;

    private string $apiKey;

	private array $tracks;

    private array $artists;

    private array $albums;

    private array $artistsTmp = [];

    private array $albumsTmp = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CsvConverter $converter,
        private readonly HttpClientInterface $httpClient,
        private readonly TrackRepository $trackRepository,
        private readonly ArtistRepository $artistRepository,
        private readonly AlbumRepository $albumRepository,
        private SerializerInterface $serializer,
        ParameterBagInterface $parameterBag
    ) {
        $this->parameters = $parameterBag->get('music_collection');
        $this->apiUrl  = $parameterBag->get('youtube_api_url');
        $this->apiKey  = $parameterBag->get('youtube_key');
		$this->tracks  = $this->trackRepository->findAll();
		$this->artists = $this->artistRepository->findAll();
		$this->albums  = $this->albumRepository->findAll();
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
        /**
        $output->writeln('<comment>Step 3. Importing Albums</comment>');

        if (!$this->importAlbums($input, $output)) {
            $io->error('No Import');

            return Command::FAILURE;
        }
**/
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

        // Define the size of record, the frequency for persisting the data and the current index of records
        $size = count($data);
        $i = 1;

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        header('Content-type: text/html; charset=UTF-8');
        // Processing on each row of data
        foreach ($data as $row) {
			
			$continue = false;
			
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
            $track->setNote(trim($row[10]) === 'No Rating' ? null : (int)$row[10]);

			$hash = hash('sha256', $track->stringify());
			foreach ($this->tracks as $key => &$trackInBase) {
				if ($trackInBase->getHash() && $trackInBase->getHash() === $hash) {
                    $continue = true;
                    if (!$trackInBase->getYoutubeKey()) {
                        $continue = false;
                        $track->setId($trackInBase->getId());
                        $serializedTrack = $this->serializer->serialize($track, 'json');
                        $track = $this->serializer->deserialize($serializedTrack, Track::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $trackInBase]);
                    }
                    unset($this->tracks[$key]);
					break;
				}
				
				if (($trackInBase->getNumero() === $track->getNumero() || $trackInBase->getTitre() === $track->getTitre())
					&& $trackInBase->getAuteur() === $track->getAuteur() && $trackInBase->getAlbum() === $track->getAlbum()
				) {
                    $track->setId($trackInBase->getId());
                    $track->setYoutubeKey($trackInBase->getYoutubeKey() ?? '');
                    $serializedTrack = $this->serializer->serialize($track, 'json');
                    $track = $this->serializer->deserialize($serializedTrack, Track::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $trackInBase]);
                    unset($this->tracks[$key]);
                    break;
				}
			}
			
			if ($continue) {
				continue;
			}

            if ($track->getNote() == 5 && !$track->getYoutubeKey()) {
                try {
                    $search = $row[0] . ' ' . $row[2];
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
                    $track->setYoutubeKey($resultYouToube[0]['id']['videoId']);
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                }
            }

            $this->entityManager->persist($track);

            if (empty($this->artistsTmp[$track->getAuteur()])) {
                $this->artistsTmp[$track->getAuteur()] = 1;
            }

            if (empty($this->albumsTmp[$track->getArtiste()][$track->getAlbum()])) {
                $this->albumsTmp[$track->getArtiste()][$track->getAlbum()] = [
                    "genre" => $track->getGenre(),
                    "annee" => $track->getAnnee()
                ];
            }

            // Each 20 items persisted we flush everything
            if (($i%$this->parameters['batch_size']) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $progress->advance($this->parameters['batch_size']);
            }

            $i ++;
        }

        $this->entityManager->flush();

		// Suppression du vieux reliquat en base non identifié
		foreach($this->tracks as $trackInBase) {
			$this->entityManager->remove($trackInBase);
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
        $size = count($this->artistsTmp);
        $i = 1;

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        foreach ($this->artistsTmp as $artisteName => $value) {
            $artiste = new Artist();
            $artiste->setName($artisteName);
            try {
                $response = $this->httpClient->request('GET',  $this->parameters['last_fm_api_url'], [
                    'query' => [
                        'api_key'  => $this->parameters['last_fm_key'],
                        'artist'   => $artisteName,
                        'method' => 'artist.getInfo',
                        'format' => 'json',
                    ],
                    'headers' => [
                        'Content-Type: application/json',
                        'Accept: application/json',
                    ]
                ]);
                $response = json_decode($response->getContent(), true) ?? [];

                $artiste->setImageUrl($response['artist']['image'][4]['#text']);
                $artiste->setBio($response['artist']['bio']['content']);
            } catch(\Exception $e) {
                $output->writeln($e->getMessage());
            }
            $this->entityManager->persist($artiste);

            // Each 20 items persisted we flush everything
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
        $size = count($this->albumsTmp);
        $i = 1;

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        foreach ($this->albumsTmp as $artisteName => $albums) {
            foreach ($albums as $albumName => $features) {
                $album = new Album();
                $album->setAuteur($artisteName);
                $album->setName($albumName);
                $album->setAnnee((int)$features['annee']);
                $album->setGenre($features['genre']);

                if (strtolower($albumName) != 'divers' && strtolower($artisteName) != 'divers') {
                    $search = $artisteName . " " . $albumName;

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
                        $response = json_decode($response->getContent(), true);
                        $album->setYoutubeKey($response['items'][0]['id']['playlistId']);
                    } catch (\Exception $e) {
                        $output->writeln($e->getMessage());
                    }
                }

                $this->entityManager->persist($album);
                // Each 20 items persisted we flush everything
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
