<?php

namespace App\Controller;

use App\Repository\FluxRepository;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function homeAction(VideoRepository $videoRepository, FluxRepository $fluxRepository): Response
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
            $i ++;

            if ($i >= ($videoParameters['index_max_anim'] + $videoParameters['index_max_music'] + $videoParameters['index_max_autre'])) {
                break;
            }
        }

        $playlist = $fluxRepository->findOneBy(['name' => 'Vu - France 2']);

        return $this->render('home.html.twig', array(
            'videos' => $videos,
            'playlist' => $playlist,
            'anims' => $anims,
            'musics' => $musics,
            'stranges' => $stranges
        ));
    }
}