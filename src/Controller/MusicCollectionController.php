<?php
namespace App\Controller;

use App\Entity\MusicCollection\Track;
use App\Form\TrackType;
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
                        'youtubeKey' => '<a id="track-youtube-'.$track->getId().'" href="https://www.youtube.com/watch?v='.$track->getYoutubeKey().'" class="video-link icon-youtube">'.($track->getYoutubeKey() ? '<i class="bi bi-youtube"></i>' : '').'</a>',
                        'id' => $track->getId(),
                        'auteur' => strtoupper($track->getAuteur()) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $track->getAuteur() . '" ><i class="bi bi-eye-fill"></i></a> ' . $track->getAuteur() : $track->getAuteur(),
                        'titre' => '<a href="#track-youtube-'.$track->getId().'" data-id='.$track->getId().' class="edit-tracks" data-action="music#openEditModal"><i class="bi bi-pencil-square"></i></a> ' . $track->getTitre(),
                        'numero' => $track->getNumero(),
                        'album' => '<a href="#" data-action="music#openModal" data-artist="' . $track->getArtiste() . '" data-album="' . $track->getAlbum() . '" ><i class="bi bi-filter-circle-fill"></i></a> ' . $track->getAlbum(),
                        'annee' => $track->getAnnee(),
                        'artiste' => strtoupper($track->getArtiste()) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $track->getArtiste() . '" ><i class="bi bi-eye-fill"></i></a> ' . $track->getArtiste() : $track->getArtiste(),
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
                    'recordsTotal' => count($trackRepository->getTracksByAlbumsAjax([], 0, false)),
                ];

                foreach ($albums as $album) {
                    $annees = array_unique(json_decode($album['annees'], true));
                    sort($annees);
                    $genres = array_unique(json_decode($album['genres'], true));
                    $output['data'][] = [
                        'album' => '<a href="#" data-action="music#openModal" data-artist="' . $album['artiste']. '" data-album="' . $album['album'] . '" ><i class="bi bi-filter-circle-fill"></i></a> ' . $album['album'],
                        'annee' => implode(', ', $annees),
                        'artiste' => strtoupper($album['artiste']) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $album['artiste'] . '" ><i class="bi bi-eye-fill"></i></a> ' . $album['artiste'] : $album['artiste'],
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

    #[Route('/track/edit/{id}', name: '_track_edit', requirements: ['id' => '\d+'])]
    public function editTrack(Track $track, Request $request, TrackRepository $trackRepository): Response
    {
        if ($request->get('trackkey')) {
            $track->setYoutubeKey($request->get('trackkey'));
            $trackRepository->save($track, true);

            return $this->redirectToRoute('music_all', ['mode' => 'tracks']);
        }

        $form = $this->createForm(TrackType::class, $track, [
            'action' => $this->generateUrl(
                $request->attributes->get('_route'),
                $request->attributes->get('_route_params')
            ),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $youtubeKey = $form->get('youtubeKey')->getData();
            $track->setYoutubeKey($youtubeKey);
            $trackRepository->save($track, true);

            return new Response(json_encode([
                'youtube_key' => $track->getYoutubeKey(),
                'id' => $track->getId(),
            ]), 200);
        }

        return $this->render('music/track_edit.html.twig', [
            'track' => $track,
            'form' => $form
        ]);
    }

    #[Route('/couch', name:'_couch')]
    public function couch(Request $request, TrackRepository $trackRepository): Response
    {
        $auteur = $request->get('auteur') ?? null;
        $album = $request->get('album') ?? null;
        $genre = $request->get('genre') ?? null;
        $note = $request->get('note') ?? null;
        $annee = $request->get('annee') ?? null;
        $search = $request->get('search') ?? null;

        $tracks = $trackRepository->getTracks($auteur, $album, $genre, $note, $annee, $search);
        //shuffle($tracks);
        $musicParameters = $this->getParameter('music_collection');
        $videoParameters = $this->getParameter('shufler_video');
        $playlist = [$videoParameters['intro_couch']];

        $i = 0;
        foreach($tracks as $track) {
            $playlist[] = $track->getYoutubeKey();
            if ($i >= $musicParameters['max_random']) {
                break;
            }
            $i++;
        }

        return $this->render('video/couch.html.twig', [
            'videos' => $playlist ?? [],
        ]);
    }


    // FRONTIERE DU TODO

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
}