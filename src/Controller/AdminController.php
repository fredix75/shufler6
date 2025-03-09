<?php

namespace App\Controller;

use App\Helper\ApiRequester;
use App\Helper\VideoHelper;
use App\Repository\MusicCollection\TrackRepository;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dead_videos', name: '_dead_videos')]
    public function deadVideos(VideoRepository $videoRepository, ApiRequester $apiRequester, VideoHelper $videoHelper): Response
    {
        $videos = $videoRepository->findBy(['published' => true]);
        $deadVideos = [];

        foreach ($videos as $video) {
            if ($videoHelper->getPlatform($video->getLien()) !== VideoHelper::YOUTUBE) {
                continue;
            }

            $key = $videoHelper->getIdentifer($video->getLien(), VideoHelper::YOUTUBE);
            $response = $apiRequester->sendRequest(VideoHelper::YOUTUBE, '/videos', [
                'part' => 'id',
                'id' => $key, 1
            ]);

            if ($response->getStatusCode() === Response::HTTP_OK) {
                $response = json_decode($response->getContent(), true);
                if (empty($response['items'])) {
                    $deadVideos[] = $video;
                }
            }
        }

        return $this->render('admin/dead_videos.html.twig', [
            'videos' => $deadVideos,
        ]);
    }

    #[Route('/stats', name: '_stats')]
    public function statistiques(TrackRepository $trackRepository): Response
    {
        $stats = $trackRepository->getTracksByCountry();

        dd($stats);

        return $this->render('admin/statistiques.html.twig');
    }
}
