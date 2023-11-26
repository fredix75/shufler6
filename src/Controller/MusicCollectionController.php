<?php
namespace App\Controller;

use App\Entity\MusicCollection\Album;
use App\Entity\MusicCollection\Track;
use App\Form\AlbumFormType;
use App\Form\FilterTracksFormType;
use App\Form\TrackFormType;
use App\Helper\ApiRequester;
use App\Helper\VideoHelper;
use App\Repository\MusicCollection\ArtistRepository;
use App\Twig\Runtime\ShuflerRuntime;
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
        ShuflerRuntime $shuflerRuntime,
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
                        'youtubeKey' => $this->renderView('music/part/_youtube_link.html.twig', [
                            'track' => $track,
                            'youtube_key' => VideoHelper::YOUTUBE_WATCH.$track->getYoutubeKey(),
                        ]),
                        'id' => $track->getId(),
                        'auteur' => strtoupper($track->getAuteur()) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $track->getAuteur() . '" onclick="return false;"><i class="bi bi-eye-fill"></i></a> ' . $track->getAuteur() : $track->getAuteur(),
                        'titre' => '<a href="#" data-id='.$track->getId().' class="edit-tracks" data-action="music#openEditModal" onclick="return false;"><i class="bi bi-pencil-square"></i></a> ' . $track->getTitre(),
                        'numero' => $track->getNumero(),
                        'album' => '<a href="#" data-action="music#openModal" data-artist="' . $track->getArtiste() . '" data-album="' . $track->getAlbum() . '" onclick="return false;"><i class="bi bi-filter-circle-fill"></i></a> ' . $track->getAlbum(),
                        'annee' => $track->getAnnee(),
                        'artiste' => strtoupper($track->getArtiste()) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $track->getArtiste() . '" onclick="return false;"><i class="bi bi-eye-fill"></i></a> ' . $track->getArtiste() : $track->getArtiste(),
                        'genre' => '<span class="badge bg-dark">' . $track->getGenre() . '</span>',
                        'duree' => $track->getDuree(),
                        'pays' => $track->getPays(),
                        'bitrate' => $track->getBitrate(),
                        'note' => $shuflerRuntime->displayStarsFunction($track->getNote() ?? 0),
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
                        'album' => '<a href="#" data-action="music#openModal" data-artist="' . $album['artiste']. '" data-album="' . $album['album'] . '" onclick="return false;"><i class="bi bi-filter-circle-fill"></i></a> ' . $album['album'],
                        'annee' => implode(', ', $annees),
                        'artiste' => strtoupper($album['artiste']) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $album['artiste'] . '" onclick="return false;"><i class="bi bi-eye-fill"></i></a> ' . $album['artiste'] : $album['artiste'],
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
        $albumName = $request->get('album');
        $isModal = $request->get('modal') ?? false;
        $tracks = $trackRepository->getTracksByAlbum($artist, $albumName);
        $album = $albumRepository->findOneBy(['auteur'=>$artist, 'name' => $albumName]);
        if (empty($album)) {
            $album['auteur'] = $artist;
            $album['name'] = $albumName;
            $album['annee'] = null;
            $album['genre'] = null;
            $album['picture'] = null;
            $album['youtubeKey'] = null;
        }
        if ($isModal) {
            return $this->render('music/part/_album.html.twig', [
                'tracks' => $tracks,
                'album' => $album,
            ]);
        }

        return $this->render('music/part/_album_content.html.twig', [
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

        $form = $this->createForm(TrackFormType::class, $track, [
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

    #[Route('/album/edit/{id}', name: '_album_edit', requirements: ['id' => '\d+'])]
    public function editAlbum(Album $album, Request $request, AlbumRepository $albumRepository): Response
    {
        if ($request->get('albumkey')) {
            $album->setYoutubeKey($request->get('albumkey'));
            $albumRepository->save($album, true);

            return $this->redirectToRoute('music_albums');
        }

        $form = $this->createForm(AlbumFormType::class, $album, [
            'action' => $this->generateUrl(
                $request->attributes->get('_route'),
                $request->attributes->get('_route_params')
            ),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $album->setYoutubeKey($form->get('youtubeKey')->getData());
            $album->setPicture($form->get('picture')->getData());
            $albumRepository->save($album, true);

            return new Response(json_encode([
                'youtube_key' => $album->getYoutubeKey(),
                'picture' => $album->getPicture(),
                'id' => $album->getId(),
            ]), 200);
        }

        return $this->render('music/album_edit.html.twig', [
            'album' => $album,
            'form' => $form
        ]);
    }

    #[Route('/couch', name:'_couch')]
    public function couch(Request $request, TrackRepository $trackRepository): Response
    {
        $params = [
            'auteur'        => $request->get('auteur') ?? null,
            'album'         => $request->get('album') ?? null,
            'genre'         => $request->get('genre') ?? null,
            'annee'         => $request->get('annee') ?? null,
            'search'        => $request->get('search') ?? null,
            'hasYoutubeKey' => true,
        ];

        $form = $this->createForm(FilterTracksFormType::class, $params);

        $params['note'] = $request->get('note') ?? null;
        $tracks = $trackRepository->getTracks($params);
        if (empty($params['album'])) {
            shuffle($tracks);
        }
        $musicParameters = $this->getParameter('music_collection');
        $videoParameters = $this->getParameter('shufler_video');
        $playlist = [$videoParameters['intro_couch']];

        $i = 0;
        foreach($tracks as $track) {
            if (!\in_array($track->getYoutubeKey(), $playlist) && $track->getYoutubeKey() !== 'nope') {
                $playlist[] = $track->getYoutubeKey();
                $i++;
            }
            if ($i >= $musicParameters['max_random']) {
                break;
            }
        }

        return $this->render('video/couch.html.twig', [
            'videos' => $playlist ?? [],
            'form_track' => $form,
        ]);
    }

    #[Route('/albums/{page}', name: '_albums', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    public function getAlbums(Request $request, AlbumRepository $albumRepository, int $page = 1): Response
    {
        $params = [
            'auteur'        => $request->get('auteur') ?? null,
            'album'         => $request->get('album') ?? null,
            'genre'         => $request->get('genre') ?? null,
            'annee'         => $request->get('annee') ?? null,
            'search'        => $request->get('search') ?? null,
            'random'        => $request->get('random') === '1',
        ];

        $form = $this->createForm(FilterTracksFormType::class, $params, ['mode' => 'album']);
        $form->handleRequest($request);

        $max = $this->getParameter('music_collection')['max_nb_albums'];
        $routeParams = $request->attributes->get('_route_params');
        if (isset($request->query->all()['page'])) {
            $page = 1;
            $routeParams['page'] = 1;
        }

        $albums = $albumRepository->getAlbums($params, $page, $max);

        if (false === $params['random']) {
            $pagination = [
                'page' => $page,
                'route' => 'music_albums',
                'pages_count' => (int)ceil(count($albums) / $max),
                'route_params' => array_merge($routeParams, $params)
            ];
        }

        return $this->render('music/albums.html.twig', [
            'albums' => $albums,
            'pagination' => $pagination ?? [],
            'form_track' => $form,
        ]);
    }

    #[Route('/link/{id}', name:'_link')]
    public function getLink(Track $track, TrackRepository $trackRepository, Request $request, ApiRequester $apiRequester): Response
    {
        $search = $request->get('auteur') . ' ' . $request->get('titre');
        $response = $apiRequester->sendRequest('youtube', '/search', [
            'q' => $search,
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $resultYouTube = json_decode($response->getContent(), true)['items'] ?? [];

            $track->setYoutubeKey($resultYouTube[0]['id']['videoId'] ?? '');
            $trackRepository->save($track, true);

            return new Response(json_encode(['youtube_key' => $track->getYoutubeKey()]), Response::HTTP_OK);
        }

        return new Response(json_encode(['fail : ' . $response->getStatusCode()]), $response->getStatusCode());
    }
}