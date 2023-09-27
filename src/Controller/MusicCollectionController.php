<?php
namespace App\Controller;

use App\Repository\MusicCollection\ArtistRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\MusicCollection\AlbumRepository;
use App\Repository\MusicCollection\TrackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/music', name: 'music')]
#[IsGranted('ROLE_ADMIN')]
class MusicCollectionController extends AbstractController
{

    #[Route('/all/{mode}', name: '_all', requirements: ['mode' => '/^[tracks|albums]$/'], default: 'tracks')]
    public function getAll(
        Request $request,
        ParameterBagInterface $parameters,
        TrackRepository $trackRepository,
        string $mode = 'tracks'
    ): Response
    {

        $columnsToDisplay = $parameters->get('music_collection')['track_columns'];
        if ($mode === 'albums') {
            $columnsToDisplay = $parameters->get('music_collection')['album_columns'];
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
                    'recordsTotal' => count($trackRepository->count([])),
                ];

                foreach ($tracks as $track) {
                    $output['data'][] = [
                        'id' => $track->getId(),
                        'auteur' => strtoupper($track->getAuteur()) !== 'DIVERS' ? '<a href="#" class="artiste_track" data-toggle="modal" data-target="#musicModal" data-artiste="' . $track->getAuteur() . '" ><span class="glyphicon glyphicon-chevron-right"></span></a> ' . $track->getAuteur() : $track->getAuteur(),
                        'titre' => $track->getTitre(),
                        'numero' => $track->getNumero(),
                        'album' => '<a href="#" class="album_tracks" data-toggle="modal" data-target="#musicModal" data-artiste="' . $track->getArtiste() . '" data-album="' . $track->getAlbum() . '" ><span class="glyphicon glyphicon-chevron-right"></span></a> ' . $track->getAlbum(),
                        'annee' => $track->getAnnee(),
                        'artiste' => strtoupper($track->getArtiste()) !== 'DIVERS' ? '<a href="#" class="artiste_track" data-toggle="modal" data-target="#musicModal" data-artiste="' . $track->getArtiste() . '" ><span class="glyphicon glyphicon-chevron-right"></span></a> ' . $track->getArtiste() : $track->getArtiste(),
                        'genre' => $track->getGenre(),
                        'duree' => $track->getDuree(),
                        'pays' => $track->getPays(),
                        'bitrate' => $track->getBitrate(),
                        'note' => $track->getNote()
                    ];
                }
            } elseif ($mode === 'albums') {

                $albums = $trackRepository->getTracksByAlbumsAjax($filters, $start, $length, $sort, $dir);

                $output = [
                    'data' => [],
                    'recordsFiltered' => count($trackRepository->getAlbumsAjax($filters, 0, false)),
                    'recordsTotal' => count($trackRepository->count([])),
                ];

                foreach ($albums as $album) {
                    $output['data'][] = [
                        'album' => '<a href="#" class="album_tracks" data-toggle="modal" data-target="#musicModal" data-artiste="' . $album->getArtiste() . '" data-album="' . $album->getAlbum() . '" ><span class="glyphicon glyphicon-chevron-right"></span></a> ' . $album->getAlbum(),
                        'annee' => $album->getAnnee(),
                        'artiste' => strtoupper($album->getArtiste()) !== 'DIVERS' ? '<a href="#" class="artiste_track" data-toggle="modal" data-target="#musicModal" data-artiste="' . $album->getArtiste() . '" ><span class="glyphicon glyphicon-chevron-right"></span></a> ' . $album->getArtiste() : $album->getArtiste(),
                        'genre' => $album->getGenre()
                    ];
                }
            }
            return new Response(json_encode($output), Response::HTTP_OK, [
                'Content-Type' => 'application/json'
            ]);
        }

        return $this->render('music/music.html.twig', [
            'display_mode' => $mode,
            'columns_db' => $columnsToDisplay,
        ]);
    }

    #[Route('/tracks_album', name: '_tracks_album')]
    public function getTracksByAlbumAjax(Request $request, TrackRepository $trackRepository)
    {
        $artist = $request->get('artist');
        $album = $request->get('album');
        $tracks = $trackRepository->getTracksByAlbum($artist, $album);
        $output = [
            'data' => []
        ];
        foreach ($tracks as $track) {
            $output['data'][] = [
                'numero' => $track->getNumero(),
                'titre' => $track->getTitre(),
                'auteur' => $track->getAuteur(),
                'duree' => $track->getDuree(),
                'annee' => $track->getAnnee()
            ];
        }
        return new Response(json_encode($output), Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/artists', name: '_artists')]
    public function getArtists(ArtistRepository $artistRepository)
    {
        $artists = $artistRepository->getArtistes();
        return $this->render('music/artists.html.twig', [
            'artists' => $artists
        ]);
    }

    #[Route('/albums', name: '_albums')]
    public function getAlbums(AlbumRepository $albumRepository)
    {
        $albums = $albumRepository->getAlbums();
        return $this->render('music/albums.html.twig', [
            'albums' => $albums
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
            'liste' => $liste
        ]);
    }

    /**
     * Get Artistes Random View of some Artists
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function artistesApiAction()
    {
        $em = $this->getDoctrine()->getManager();
        $artistes = $em->getRepository('SHUFLERShuflerBundle:Artiste')->getArtistes('youtube');

        shuffle($artistes);

        $liste = [];
        foreach ($artistes as $key => $artiste) {
            if ($key >= 500)
                break;

            $liste[] = $artiste;
        }

        return $this->render('SHUFLERShuflerBundle:Music:artistesApi.html.twig', array(
            'liste' => $liste
        ));
    }

    /**
     * Get Random Tracks
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getRandomTracksAction(Request $request)
    {
        $genre = $request->query->get('genre');
        $note = $request->query->get('note');
        $annee = $request->query->get('annee');
        $search = $request->query->get('search');

        $tracks = $this->getDoctrine()
            ->getManager()
            ->getRepository('SHUFLERShuflerBundle:MusicTrack')
            ->getTracks($genre, $note, $annee, $search);

        if (empty($tracks)) {
            $tracks = $this->getDoctrine()
                ->getManager()
                ->getRepository('SHUFLERShuflerBundle:MusicTrack')
                ->getTracks();
        }
        shuffle($tracks);

        $liste = "";
        foreach ($tracks as $key => $track) {
            if ($key == 1) {
                $single = $track->getYoutubeKey();
                continue;
            }
            if ($key > 100)
                break;

            $liste .= $track->getYoutubeKey() . ',';
        }

        return $this->render('SHUFLERShuflerBundle:Music:musicList.html.twig', array(
            'single' => $single,
            'liste' => $liste
        ));
    }
}