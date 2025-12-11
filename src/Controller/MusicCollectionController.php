<?php

namespace App\Controller;

use App\Entity\MusicCollection\Album;
use App\Entity\MusicCollection\CloudTrack;
use App\Entity\MusicCollection\Track;
use App\Form\AlbumFormType;
use App\Form\CloudTrackFormType;
use App\Form\FilterTracksFormType;
use App\Form\TrackFormType;
use App\Helper\ApiRequester;
use App\Helper\VideoHelper;
use App\Repository\FilterPieceRepository;
use App\Repository\MusicCollection\ArtistRepository;
use App\Repository\MusicCollection\CloudAlbumRepository;
use App\Repository\MusicCollection\CloudTrackRepository;
use App\Repository\MusicCollection\PieceRepository;
use App\Twig\Runtime\ShuflerRuntime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\MusicCollection\AlbumRepository;
use App\Repository\MusicCollection\TrackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/music', name: 'music')]
#[IsGranted('ROLE_ADMIN')]
class MusicCollectionController extends AbstractController
{
    #[Route('/all/{mode}', name: '_all', requirements: ['mode' => 'tracks|albums'], defaults: ['mode' => 'tracks'])]
    public function getAll(
        Request               $request,
        ParameterBagInterface $parameters,
        TrackRepository       $trackRepository,
        ShuflerRuntime        $shuflerRuntime,
        string                $mode
    ): Response
    {
        $columnsToDisplay = $parameters->get('music_collection')['track_fields'];
        if ($mode === 'albums') {
            $columnsToDisplay = $parameters->get('music_collection')['album_fields'];
        }

        if ($request->isXmlHttpRequest()) {
            $parameters = $request->query->all();
            $length = $parameters['length'];
            $length = $length && ($length > 0) ? $length : 0;

            $start = $parameters['start'];
            $start = $length ? ($start && $start > 0 ? $start : 0) / $length : 0;

            $search = $parameters['search'];

            $filters = [
                'query' => @$search['value']
            ];

            $sort = $parameters['order'][0]['column'];
            $sort = $columnsToDisplay[$sort];

            $dir = @$parameters['order'][0]['dir'];

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
                        ]),
                        'id' => $track->getId(),
                        'auteur' => strtoupper($track->getAuteur()) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $track->getAuteur() . '" onclick="return false;"><i class="bi bi-eye-fill"></i></a> ' . $track->getAuteur() : $track->getAuteur(),
                        'titre' => '<a href="#" data-id=' . $track->getId() . ' class="edit-tracks" data-action="music#openEditModal" onclick="return false;"><i class="bi bi-pencil-square"></i></a> ' . $track->getTitre(),
                        'numero' => $track->getNumero(),
                        'album' => '<a href="#" data-action="music#openModal" data-artist="' . $track->getArtiste() . '" data-album="' . $track->getAlbum() . '" onclick="return false;"><i class="bi bi-filter-circle-fill"></i></a> ' . $track->getAlbum(),
                        'annee' => $track->getAnnee(),
                        'artiste' => strtoupper($track->getArtiste()) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $track->getArtiste() . '" onclick="return false;"><i class="bi bi-eye-fill"></i></a> ' . $track->getArtiste() : $track->getArtiste(),
                        'genre' => '<span class="badge bg-dark">' . $track->getGenre() . '</span>',
                        'duree' => $track->getDuree(),
                        'pays' => $track->getPays(),
                        'bitrate' => $track->getBitrate(),
                        'note' => $track->getNote() ? $shuflerRuntime->displayStarsFunction($track->getNote()) : '',
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
                        'youtubeKey' => $this->renderView('music/part/_youtube_pl_link.html.twig', [
                            'album' => $album,
                            'youtube_key' => VideoHelper::YOUTUBE_WATCH . $album['youtubeKey'],
                        ]),
                        'album' => '<a href="#" data-action="music#openModal" data-artist="' . $album['artiste'] . '" data-album="' . $album['album'] . '" onclick="return false;"><i class="bi bi-filter-circle-fill"></i></a> ' . $album['album'],
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
            'path_url' => $this->generateUrl('music_all', ['mode' => $mode]),
            'page_length' => $parameters->get('music_collection')['music_all_nb_b_page'],
            'columns_db' => $columnsToDisplay,
        ]);
    }

    #[Route('/cloud-all/{mode}', name: '_cloud-all', requirements: ['mode' => 'tracks|albums'], defaults: ['mode' => 'tracks'])]
    public function getCloudAll(
        Request               $request,
        ParameterBagInterface $parameters,
        CloudTrackRepository  $cloudTrackRepository,
        CloudAlbumRepository  $cloudAlbumRepository,
        ShuflerRuntime        $shuflerRuntime,
        string                $mode
    ): Response
    {
        $columnsToDisplay = $parameters->get('music_collection')['cloud-track_fields'];
        if ($mode === 'albums') {
            $columnsToDisplay = $parameters->get('music_collection')['cloud-album_fields'];
        }

        if ($request->isXmlHttpRequest()) {
            $parameters = $request->query->all();
            $length = $parameters['length'];
            $length = $length && ($length > 0) ? $length : 0;

            $start = $parameters['start'];
            $start = $length ? ($start && $start > 0 ? $start : 0) / $length : 0;

            $search = $parameters['search'];

            $filters = [
                'query' => @$search['value']
            ];

            $sort = $parameters['order'][0]['column'];
            $sort = $columnsToDisplay[$sort];

            $dir = @$parameters['order'][0]['dir'];

            if ($mode === 'tracks') {
                $tracks = $cloudTrackRepository->getTracksAjax($filters, $start, $length, $sort, $dir);

                $output = [
                    'data' => [],
                    'recordsFiltered' => count($cloudTrackRepository->getTracksAjax($filters, 0, false)),
                    'recordsTotal' => $cloudTrackRepository->count([]),
                ];

                foreach ($tracks as $track) {
                    $output['data'][] = [
                        'youtubeKey' => $this->renderView('music/part/_youtube_link.html.twig', [
                            'track' => $track
                        ]),
                        'id' => $track->getId(),
                        'auteur' => strtoupper($track->getAuteur()) !== 'DIVERS' ? '<a href="#" data-action="music#openModal" data-artist="' . $track->getAuteur() . '" onclick="return false;"><i class="bi bi-eye-fill"></i></a> ' . $track->getAuteur() : $track->getAuteur(),
                        'titre' => '<a href="'.$this->generateUrl('music_cloudtrack_edit',  ['id' => $track->getId()]).'"><i class="bi bi-pencil-square"></i></a> ' . $track->getTitre(),
                        'annee' => $track->getAnnee(),
                        'genre' => '<span class="badge bg-dark">' . $track->getGenre() . '</span>',
                        'pays'  => $track->getPays(),
                        'note' => $track->getNote() ? $shuflerRuntime->displayStarsFunction($track->getNote()) : '',
                    ];
                }
            } elseif ($mode === 'albums') {

                $albums = $cloudAlbumRepository->getAlbumsAjax($filters, $start, $length, $sort, $dir);

                $output = [
                    'data' => [],
                    'recordsFiltered' => count($cloudAlbumRepository->getAlbumsAjax($filters, 0, false)),
                    'recordsTotal' => count($cloudAlbumRepository->getAlbumsAjax([], 0, false)),
                ];

                foreach ($albums as $album) {
                    $output['data'][] = [
                        'youtubeKey' => $this->renderView('music/part/_youtube_pl_link.html.twig', [
                            'album' => $album,
                            'youtube_key' => VideoHelper::YOUTUBE_WATCH . $album->getYoutubekey(),
                        ]),
                        'name' => '<a href="'.$this->generateUrl('music_album_cloud_edit', ['id' => $album->getId()]).'"><i class="bi bi-pencil-square"></i></a> ' . $album->getName(),
                        'auteur' => $album->getAuteur(),
                        'annee' => $album->getAnnee(),
                        'genre' => $album->getGenre(),
                    ];
                }
            }

            return new Response($this->json($output)->getContent(), Response::HTTP_OK, [
                'Content-Type' => 'application/json'
            ]);
        }

        return $this->render('music/music.html.twig', [
            'path_url' => $this->generateUrl('music_cloud-all', ['mode' => $mode]),
            'page_length' => $parameters->get('music_collection')['music_all_nb_b_page'],
            'columns_db' => $columnsToDisplay,
        ]);
    }

    #[Route('/artist', name: '_artist')]
    public function getArtist(Request $request, ArtistRepository $artistRepository): Response
    {
        $artist = $request->query->get('artist');
        $artist = $artistRepository->findOneBy(['name' => $artist]);

        return $this->render('music/part/_artist.html.twig', [
            'artist' => $artist
        ]);
    }

    #[Route('/tracks_album', name: '_tracks_album')]
    public function getTracksByAlbumAjax(Request $request, TrackRepository $trackRepository, AlbumRepository $albumRepository): Response
    {
        $artist = $request->query->get('artist');
        $albumName = $request->query->get('album');
        $isModal = $request->query->get('modal') ?? false;
        $tracks = $trackRepository->getTracksByAlbum($artist, $albumName);
        $album = $albumRepository->findOneBy(['auteur' => $artist, 'name' => $albumName]);

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
        if ($request->request->get('trackkey')) {
            $track->setYoutubeKey($request->request->get('trackkey'));
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



    #[Route('/filter-couch', name: '_filter_couch')]
    public function filterCouch(FilterPieceRepository $filterPieceRepository): Response {

        $form = $this->createForm(FilterTracksFormType::class);

        $filterlistes = $filterPieceRepository->findBy([], ['name' => 'ASC']);

        return $this->render('music/filter_couch.html.twig', [
            'form' => $form,
            'filter_listes' => $filterlistes,
        ]);
    }

    #[Route('/couch', name: '_couch')]
    public function couch(Request $request, PieceRepository $pieceRepository): Response
    {
        $p = $request->query->all();
        $params = [
            'auteur' => $p['auteur'] ?? null,
            'album' => $p['album'] ?? null,
            'genres' => $p['genres'] ?? null,
            'annee' => $p['annee'] ?? null,
            'search' => $p['search'] ?? null,
            'hasYoutubeKey' => true,
        ];

        $form = $this->createForm(FilterTracksFormType::class, $params);

        $params['note'] = $request->query->get('note') ?? null;
        $pieces = $pieceRepository->getPieces($params);

        if (empty($params['album'])) {
            shuffle($pieces);
        }
        $musicParameters = $this->getParameter('music_collection');
        $videoParameters = $this->getParameter('shufler_video');
        $trackIntro = [
            'titre' => ' * * * * * L O A D I N G * * * * * ',
            'auteur' => '',
            'album' => '',
            'annee' => null,
            'youtubeKey' => $videoParameters['intro_couch'],
        ];

        $playlist = [$trackIntro['youtubeKey']];
        $list = [$trackIntro];

        $i = 0;
        foreach ($pieces as $piece) {
            if (!\in_array($piece['youtubeKey'], $playlist) && $piece['youtubeKey'] !== 'nope') {
                $playlist[] = $piece['youtubeKey'];
                $list[] = $piece;
                $i++;
            }
            if ($i >= $musicParameters['max_random']) {
                break;
            }
        }

        return $this->render('video/couch.html.twig', [
            'list' => $list,
            'videos' => $playlist,
            'form_track' => $form,
        ]);
    }

    #[Route('/link/{id}', name: '_link')]
    public function getLink(Track $track, TrackRepository $trackRepository, Request $request, ApiRequester $apiRequester): Response
    {
        $search = $request->request->get('auteur') . ' ' . $request->request->get('titre');
        $response = $apiRequester->sendRequest(VideoHelper::YOUTUBE, '/search', [
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

    #[Route('/playlist-link/{id}', name: '_playlist_link')]
    public function getPlaylistLink(Album $album, AlbumRepository $albumRepository, Request $request, ApiRequester $apiRequester): Response
    {
        if (strtolower($request->request->get('name')) === 'divers') {
            $album->setYoutubeKey('nope');
            $albumRepository->save($album, true);

            return new Response(json_encode(['youtube_key' => 'nope']), Response::HTTP_OK);
        }

        $search = (strtolower($request->request->get('auteur')) !== 'divers' ? $request->request->get('auteur') . ' ' : '') . $request->request->get('name');

        $response = $apiRequester->sendRequest(VideoHelper::YOUTUBE, '/search', [
            'q' => $search,
            'type' => 'playlist'
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $resultYouTube = json_decode($response->getContent(), true)['items'] ?? [];

            $album->setYoutubeKey($resultYouTube[0]['id']['playlistId'] ?? '');
            $albumRepository->save($album, true);

            return new Response(json_encode(['youtube_key' => $album->getYoutubeKey()]), Response::HTTP_OK);
        }

        return new Response(json_encode(['fail : ' . $response->getStatusCode()]), $response->getStatusCode());
    }

    #[Route('/set-extra-note/{id}', name: '_set_extra_note')]
    public function setExtraNote(Track $track, EntityManagerInterface $em): Response {
        $note = ($track->getNote() > 0 || $track->getExtraNote() > 0) && $track->getExtraNote() != -1 ? -1 : 4;
        $track->setExtraNote($note);
        $em->flush();

        return new JsonResponse(['note' => $note]);
    }
}
