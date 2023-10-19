<?php

namespace App\Command;

use App\Entity\MusicCollection\Album;
use App\Entity\MusicCollection\Artist;
use App\Entity\MusicCollection\Track;
use App\Helper\CsvConverter;
use App\Repository\MusicCollection\AlbumRepository;
use App\Repository\MusicCollection\ArtistRepository;
use App\Repository\MusicCollection\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
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
        private readonly SerializerInterface $serializer,
        ParameterBagInterface $parameterBag,
        string $name = null
    ) {
        $this->parameters = $parameterBag->get('music_collection');
        $this->apiUrl  = $parameterBag->get('youtube_api_url');
        $this->apiKey  = $parameterBag->get('youtube_key');
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();
        $io->writeln(sprintf('<comment>--- Start: %s ---</comment>', $now->format('d-m-Y G:i:s')));
        $io->writeln('<comment>Searchin\' File music.csv</comment>');
        $io->writeln('<comment>Step 1. Importing Tracks</comment>');

        if (!$this->importTracks($input, $output)) {
            $io->error('No Import : Check the File, Dude!');

            return Command::FAILURE;
        }

        $io->writeln('<comment>Step 2. Importing Artists</comment>');

        if (!$this->importArtists($input, $output)) {
            $io->error('No Import');

            return Command::FAILURE;
        }

        $io->writeln('<comment>Step 3. Importing Albums</comment>');

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

        $minimalNoteToRequest = 4;
        $size = count($data);
        $i = $n = 0;

        $this->tracks  = $this->trackRepository
            ->createQueryBuilder('t')
            ->orderBy('t.titre', 'ASC')
            ->addOrderBy('t.auteur', 'ASC')
            ->addOrderBy('t.numero', 'ASC')
            ->addOrderBy('t.album', 'ASC')
            ->getQuery()->getResult();

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        header('Content-type: text/html; charset=UTF-8');
        // Processing on each row of data
        foreach ($data as $row) {
            $track = new Track();
            $track->setAuteur($row[0]);
            $track->setNumero(is_numeric($row[1]) ? (int)$row[1] : null);
            $track->setTitre($row[2]);
            $row[3] = ((int)$row[3] * 1 == 0) ? '' : $row[3];
            $track->setAnnee(is_numeric($row[3]) ? (int)$row[3] : '');
            $track->setAlbum($row[4]);
            $track->setArtiste($row[5]);
            $track->setGenre(strtolower($row[6]));
            $row[7] = (trim($row[7]) === 'Éditeur Inconnu') ? '' : $row[7];
            $track->setPays(substr($row[7], 0, 3));
            $track->setDuree(substr($row[8], 0, 10));
            $track->setBitrate(substr($row[9], 0, 10));
            $track->setNote(trim($row[10]) === 'No Rating' ? null : (int)$row[10]);

			$track->setHash($track->doHash());

            $trackExists = false;
			foreach ($this->tracks as $key => $trackInBase) {
				if ($trackInBase->getHash() && $trackInBase->getHash() === $track->getHash()) {
                    if (!$trackInBase->getYoutubeKey() && $track->getNote() == $minimalNoteToRequest) {
                        $track->setId($trackInBase->getId());
                        $serializedTrack = $this->serializer->serialize($track, 'json');
                        $track = $this->serializer->deserialize($serializedTrack, Track::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $trackInBase]);
                    } else {
                        $trackExists = true;
                        $n++;
                    }
                    unset($this->tracks[$key]);
					break;
				}

				if (((int)$trackInBase->getNumero() === (int)$track->getNumero() || strtolower($trackInBase->getTitre()) === strtolower($track->getTitre()))
					&& strtolower($trackInBase->getAuteur()) === strtolower($track->getAuteur()) && strtolower($trackInBase->getAlbum()) === strtolower($track->getAlbum())
				) {
                    $track->setId($trackInBase->getId());
                    $track->setYoutubeKey($trackInBase->getYoutubeKey() ?? '');
                    $serializedTrack = $this->serializer->serialize($track, 'json');
                    $track = $this->serializer->deserialize($serializedTrack, Track::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $trackInBase]);
                    unset($this->tracks[$key]);
                    break;
				}
			}

            if ($track->getNote() == $minimalNoteToRequest && !$track->getYoutubeKey() && !$trackExists) {
                try {
                    $search = $row[0] . ' ' . $row[2];
                    $response = $this->httpClient->request('GET', sprintf('%s/search', $this->apiUrl), [
                        'query' => [
                            'key'       => $this->apiKey,
                            'q'         => $search,
                            'part'      => 'snippet',
                            'maxResults'=> 1,
                        ],
                        'headers' => [
                            'Content-Type: application/json',
                            'Accept: application/json',
                        ]
                    ]);

                    $resultYouTube = json_decode($response->getContent(), true)['items'] ?? [];
                    $track->setYoutubeKey($resultYouTube[0]['id']['videoId'] ?? '');
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                }
            }

            if (!$trackExists) {
                $i++;
                $n++;
                $this->entityManager->persist($track);
            }

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
            if ($i%$this->parameters['batch_size'] === 0) {
                $this->entityManager->flush();
            }

            if ($n%$this->parameters['batch_size'] === 0) {
                $progress->advance($n);
                $n=0;
            }
        }

        $this->entityManager->flush();

		// Suppression du vieux reliquat en base non identifié
        $i = 0;
		foreach($this->tracks as $trackInBase) {
            $i++;
			$this->entityManager->remove($trackInBase);
            if (($i%$this->parameters['batch_size']) === 0) {
                $this->entityManager->flush();
            }
		}
		$this->entityManager->flush();

		$this->entityManager->clear();
        unset($this->tracks);
        $progress->finish();

        return true;
    }

    protected function importArtists(InputInterface $input, OutputInterface $output): bool
    {
        // Define the size of record, the frequency for persisting the data and the current index of records
        $size = count($this->artistsTmp);
        $i = 1;
        $this->artists = $this->artistRepository->findAll();

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        foreach ($this->artists as $key => $artist) {
            if (\key_exists($artist->getName(), $this->artistsTmp)) {
                unset($this->artistsTmp[$artist->getName()]);
                unset($this->artists[$key]);
            }
        }

        foreach ($this->artistsTmp as $artistName => $value) {
            $artist = new Artist();
            $artist->setName($artistName);
            try {
                $response = $this->httpClient->request('GET',  $this->parameters['last_fm_api_url'], [
                    'query' => [
                        'api_key'  => $this->parameters['last_fm_key'],
                        'artist'   => $artistName,
                        'method' => 'artist.getInfo',
                        'format' => 'json',
                    ],
                    'headers' => [
                        'Content-Type: application/json',
                        'Accept: application/json',
                    ]
                ]);

                $response = json_decode($response->getContent(), true) ?? [];

                $artist->setImageUrl($response['artist'] ? $response['artist']['image'][4]['#text'] : '');
                $artist->setBio($response['artist'] ? $response['artist']['bio']['content'] : '');
            } catch(\Exception $e) {
                $output->writeln($e->getMessage());
            }
            $this->entityManager->persist($artist);

            // Each 20 items persisted we flush everything
            if (($i%$this->parameters['batch_size']) === 0) {
                $this->entityManager->flush();
                $progress->advance($this->parameters['batch_size']);
            }

            $i ++;
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        // Suppression du vieux reliquat en base non identifié
        $i = 0;
        foreach($this->artists as $artist) {
            $i++;
            $this->entityManager->remove($artist);
            if (($i%$this->parameters['batch_size']) === 0) {
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
        unset($this->artists);
        unset($this->artistsTmp);
        $progress->finish();

        return true;
    }

    protected function importAlbums(InputInterface $input, OutputInterface $output): bool
    {
        // Define the size of record, the frequency for persisting the data and the current index of records
        $size = count($this->albumsTmp);
        $i = 1;
        $this->albums  = $this->albumRepository->findAll();

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        foreach($this->albums as $key => &$album) {
            if (\array_key_exists($album->getAuteur(), $this->albumsTmp)) {
                if (\array_key_exists($album->getName(), $this->albumsTmp[$album->getAuteur()]) && $album->getYoutubeKey()) {
                    unset($this->albumsTmp[$album->getAuteur()][$album->getName()]);
                    unset($this->albums[$key]);
                } elseif(\array_key_exists($album->getName(), $this->albumsTmp[$album->getAuteur()]) && $album->getPicture()) {
                    $this->albumsTmp[$album->getAuteur()][$album->getName()]['picture'] = $album->getPicture();
                }

            }
        }

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

                    if (empty($features['picture'])) {
                        try {
                            $response = $this->httpClient->request('GET',  $this->parameters['last_fm_api_url'], [
                                'query' => [
                                    'api_key'  => $this->parameters['last_fm_key'],
                                    'artist'   => $album->getAuteur(),
                                    'album'   => $album->getName(),
                                    'method' => 'album.getInfo',
                                    'format' => 'json',
                                ],
                                'headers' => [
                                    'Content-Type: application/json',
                                    'Accept: application/json',
                                ]
                            ]);
                            $response = json_decode($response->getContent(), true) ?? [];

                            $album->setPicture($response['album'] ? $response['album']['image'][4]['#text'] : '');
                        } catch(\Exception $e) {
                            $output->writeln($e->getMessage());
                        }
                    } else {
                        $album->setPicture($features['picture']);
                    }
                }

                $this->entityManager->persist($album);
                // Each 20 items persisted we flush everything
                if (($i%$this->parameters['batch_size']) === 0) {
                    $this->entityManager->flush();
                    $progress->advance($this->parameters['batch_size']);
                }

                $i ++;
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        // Suppression du vieux reliquat en base non identifié
        $i = 0;
        foreach($this->albums as $album) {
            $i++;
            $this->entityManager->remove($album);
            if (($i%$this->parameters['batch_size']) === 0) {
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
        unset($this->album);
        unset($this->albumTmp);
        $progress->finish();

        return true;
    }

    protected function getDatas(): array
    {
        return $this->converter->setFilePath($this->parameters['csv_path'])
            ->convert(false, ';');
    }
}
