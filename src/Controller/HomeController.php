<?php

namespace App\Controller;

use App\Repository\FluxRepository;
use App\Repository\MusicCollection\TrackRepository;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'main')]
class HomeController extends AbstractController
{
    #[Route('', name: '_home')]
    public function home(VideoRepository $videoRepository, FluxRepository $fluxRepository): Response
    {
        $videos = $videoRepository->getRandomVideos();
        $anims = $musics = $stranges = [];
        $videoParameters = $this->getParameter('shufler_video');




        $i = 0;
        foreach ($videos as $key => $video) {
            if (($video->getCategorie() === 1 || $video->getCategorie() === 9) && count($anims) < $videoParameters['index_max_anim']) {
                $anims[] = $video;
                unset($videos[$key]);
            } elseif ($video->getCategorie() === 2 && count($musics) < $videoParameters['index_max_music']) {
                $musics[] = $video;
                unset($videos[$key]);
            } elseif (($video->getCategorie() === 3 || $video->getCategorie() === 4) && count($stranges) < $videoParameters['index_max_autre']) {
                $stranges[] = $video;
                unset($videos[$key]);
            } else {
                continue;
            }
            $i++;

            if ($i >= ($videoParameters['index_max_anim'] + $videoParameters['index_max_music'] + $videoParameters['index_max_autre'])) {
                break;
            }
        }

        $playlist = $fluxRepository->findOneBy(['name' => 'Vu - France 2']);

        return $this->render('home.html.twig', [
            'videos' => $videos,
            'playlist' => $playlist,
            'anims' => $anims,
            'musics' => $musics,
            'stranges' => $stranges
        ]);
    }

    #[Route('/search/{page}', name: '_search', requirements: ['id' => '\d+'])]
    public function search(Request $request, VideoRepository $videoRepository, TrackRepository $trackRepository, int $page = 1): Response
    {
        $search = $request->get('search_field');

        $videos = $search ? $videoRepository->searchVideos($search, $page, $this->getParameter('shufler_video')['max_list']) : [];
        $videosCount = count($videos);
        $pagination = [
            'search_field' => $search,
            'page' => $page,
            'route' => 'main_search',
            'pages_count' => ceil($videosCount / $this->getParameter('shufler_video')['max_list']),
            'route_params' => [
                'search_field' => $search
            ]
        ];

        $tracks = $trackRepository->getTracks(['search' => $search]);

        return $this->render('main/search.html.twig', [
            'search' => $search,
            'pagination' => $pagination,
            'videos_count' => $videosCount,
            'videos' => $videos,
            'tracks' => $tracks,
            'track_columns' => $this->getParameter('music_collection')['track_fields'],
        ]);
    }
}