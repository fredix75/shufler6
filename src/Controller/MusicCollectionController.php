<?php
namespace App\Controller;

use App\Repository\MusicCollection\ArtistRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\MusicCollection\AlbumRepository;
use App\Repository\MusicCollection\TrackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/music', name: 'music')]
#[IsGranted('ROLE_ADMIN')]
class MusicCollectionController extends AbstractController
{

    #[Route('/all/{mode}', name: '_all', requirements: ['mode' => 'tracks|albums'], defaults: ['mode' => 'tracks'])]
    public function getAll(
        Request $request,
        ParameterBagInterface $parameters,
        TrackRepository $trackRepository,
        string $mode = 'tracks'
    ): Response
    {
        $columnsToDisplay = $parameters->get('music_collection')['track_fields'];
        if ($mode === 'albums') {
            $columnsToDisplay = $parameters->get('music_collection')['album_fields'];
        }

        if ($request->isXmlHttpRequest()) {

            $length = $request->get('length');
            $length = $length && ($length > 0) ? $length : 0;

            $start = $request->get('start');
            $start = $length ? ($start && $start > 0 ? $start : 0) / $length : 0;

            $search = $request->get('search');
            $filters = [
                'query' => @$search['value']
            ];

            $sort = $request->get('order')[0]['column'];
            $sort = $columnsToDisplay[$sort];

            $dir = @$request->get('order')[0]['dir'];

            if ($mode === 'tracks') {
                $tracks = $trackRepository->getTracksAjax($filters, $start, $length, $sort, $dir);

                $output = [
                    'data' => [],
                    'recordsFiltered' => count($trackRepository->getTracksAjax($filters, 0, false)),
                    'recordsTotal' => $trackRepository->count([]),
                ];

                foreach ($tracks as $track) {
                    $output['data'][] = [
                        'youtubeKey' => $track->getYoutubeKey() ? '<a href="https://www.youtube.com/watch?v='.$track->getYoutubeKey().'" class="video-link icon-youtube"><i class="bi bi-youtube"></i></a>' : '',
                        'id' => $track->getId(),
                        'auteur' => strtoupper($track->getAuteur()) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $track->getAuteur() . '" ><i class="bi bi-chevron-right"></i></a> ' . $track->getAuteur() : $track->getAuteur(),
                        'titre' => $track->getTitre(),
                        'numero' => $track->getNumero(),
                        'album' => '<a href="#" data-action="music#openModal" data-artist="' . $track->getArtiste() . '" data-album="' . $track->getAlbum() . '" ><i class="bi bi-chevron-right"></i></a> ' . $track->getAlbum(),
                        'annee' => $track->getAnnee(),
                        'artiste' => strtoupper($track->getArtiste()) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $track->getArtiste() . '" ><i class="bi bi-chevron-right"></i></a> ' . $track->getArtiste() : $track->getArtiste(),
                        'genre' => $track->getGenre(),
                        'duree' => $track->getDuree(),
                        'pays' => $track->getPays(),
                        'bitrate' => $track->getBitrate(),
                        'note' => $track->getNote() != 0 ? $track->getNote() : ''
                    ];
                }
            } elseif ($mode === 'albums') {

                $albums = $trackRepository->getTracksByAlbumsAjax($filters, $start, $length, $sort, $dir);

                $output = [
                    'data' => [],
                    'recordsFiltered' => count($trackRepository->getTracksByAlbumsAjax($filters, 0, false)),
                    'recordsTotal' => $trackRepository->getCountTracksByAlbumsAjax(),
                ];

                foreach ($albums as $album) {
                    $annees = array_unique(json_decode($album['annees'], true));
                    sort($annees);
                    $genres = array_unique(json_decode($album['genres'], true));
                    $output['data'][] = [
                        'album' => '<a href="#" class="album_tracks" data-toggle="modal" data-target="#musicModal" data-artiste="' . $album['artiste']. '" data-album="' . $album['album'] . '" ><span class="glyphicon glyphicon-chevron-right"></span></a> ' . $album['album'],
                        'annee' => implode(', ', $annees),
                        'artiste' => strtoupper($album['artiste']) !== 'DIVERS' ? '<a href="#" class="artiste_track" data-toggle="modal" data-target="#musicModal" data-artiste="' . $album['artiste'] . '" ><span class="glyphicon glyphicon-chevron-right"></span></a> ' . $album['artiste'] : $album['artiste'],
                        'genre' => implode(', ', $genres),
                    ];
                }
            }

            return new Response($this->json($output)->getContent(), Response::HTTP_OK, [
                'Content-Type' => 'application/json'
            ]);
        }

        return $this->render('music/music.html.twig', [
            'path_url' => $this->generateUrl('music_all', ['mode'=> $mode]),
            'page_length' => $parameters->get('music_collection')['music_all_nb_b_page'],
            'columns_db' => $columnsToDisplay,
        ]);
    }

    #[Route('/artist', name:'_artist')]
    public function getArtist(Request $request, ArtistRepository $artistRepository): Response
    {
        $artist = $request->get('artist');
        $artist = $artistRepository->findOneBy(['name' => $artist]);

        return $this->render('music/part/_artist.html.twig', [
            'artist' => $artist
        ]);
    }

    #[Route('/tracks_album', name: '_tracks_album')]
    public function getTracksByAlbumAjax(Request $request, TrackRepository $trackRepository, AlbumRepository $albumRepository): Response
    {
        $artist = $request->get('artist');
        $album = $request->get('album');
        $tracks = $trackRepository->getTracksByAlbum($artist, $album);
        $album = $albumRepository->findOneBy(['auteur'=>$artist, 'name' => $album]);
        return $this->render('music/part/_album.html.twig', [
            'tracks' => $tracks,
            'album' => $album,
        ]);
    }

    #[Route('/artists', name: '_artists')]
    public function getArtists(ArtistRepository $artistRepository)
    {
        $artists = $artistRepository->getArtistes();
        return $this->render('music/artists.html.twig', [
            'artists' => $artists,
        ]);
    }

    #[Route('/albums', name: '_albums')]
    public function getAlbums(AlbumRepository $albumRepository)
    {
        $albums = $albumRepository->getAlbums();
        return $this->render('music/albums.html.twig', [
            'albums' => $albums,
        ]);
    }

    #[Route('/albums_api', name: '_albums_api')]
    public function albumsApi(AlbumRepository $albumRepository, ParameterBagInterface $parameterBag)
    {
        $albums = $albumRepository->getAlbums(true);
        shuffle($albums);

        $liste = [];
        foreach ($albums as $key => $album) {
            if ($key > $parameterBag->get('music_collection')['max_nb_albums'] - 1)
                break;
            $liste[$album->getId()] = $album->getYoutubeKey();
        }

        return $this->render('music/albums_api.html.twig', [
            'liste' => $liste,
        ]);
    }

    #[Route('/artists_api', name: '_artists_api')]
    public function artistsApi(ArtistRepository $artistRepository, ParameterBagInterface $parameterBag): Response
    {
        $artists = $artistRepository->getArtistes();

        shuffle($artists);

        $liste = [];
        foreach ($artists as $key => $artist) {
            if ($key > $parameterBag->get('music_collection')['max_nb_artists'])
                break;

            $liste[] = $artist;
        }

        return $this->render('music/artistes_api.html.twig', [
            'liste' => $liste,
        ]);
    }

    public function getRandomTracks(Request $request, TrackRepository $trackRepository, ParameterBagInterface $parameterBag)
    {
        $genre = $request->query->get('genre');
        $note = $request->query->get('note');
        $annee = $request->query->get('annee');
        $search = $request->query->get('search');

        $tracks = $trackRepository->getTracks($genre, $note, $annee, $search);

        shuffle($tracks);

        $liste = "";
        foreach ($tracks as $key => $track) {
            if ($key == 1) {
                $single = $track->getYoutubeKey();
                continue;
            }
            if ($key > $parameterBag->get('music_collection')['max_random'])
                break;

            $liste .= $track->getYoutubeKey() . ',';
        }

        return $this->render('music/music_list.html.twig', [
            'single' => $single,
            'liste' => $liste,
        ]);
    }
}