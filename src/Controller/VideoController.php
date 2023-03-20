<?php

namespace App\Controller;

use App\Repository\VideoRepository;
use App\Twig\ShuflerExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/video', name: 'video_')]
#[Security("is_granted('ROLE_USER')")]
class VideoController extends AbstractController
{
    #[Route('/list/{categorie}/{genre}/{periode}/{page}', name: 'list', requirements: ['categorie' => '\d+', 'genre' => '\d+|-\d+', 'page' => '\d+'])]
    public function listAction(
        Request $request,
        VideoRepository $videoRepository,
        int $categorie = 0,
        int $genre = 0,
        string $periode = '0',
        int $page = 1
    ): Response
    {
        $videoParameters = $this->getParameter('shufler_video');
        $videos = $videoRepository->getPaginatedVideos($categorie, $genre, $periode, $page, $videoParameters['max_list']);
        $videosCount = count($videos);

        $pagination = [
            'page' => $page,
            'route' => 'video_list',
            'pages_count' => (int)ceil($videosCount / $videoParameters['max_list']),
            'route_params' => $request->attributes->get('_route_params')
        ];

        return $this->render('video/list.html.twig', [
            'videos' => $videos,
            'categories' => [
                    0 => 'ALL'
                ] + $videoParameters['categories'],
            'genres' => [
                    0 => 'ALL'
                ] + $videoParameters['genres'],
            'periodes' => [
                    0 => 'ALL'
                ] + array_combine($videoParameters['periods'], $videoParameters['periods']),
            'pagination' => $pagination
        ]);
    }

    #[Route('/couch/{categorie}/{genre}/{periode}', name: 'couch', requirements: ['categorie' => '\d+', 'genre' => '\d+|-\d+'])]
    public function couchAction(
        VideoRepository $videoRepository,
        ShuflerExtension $shuflerExtension,
        int $categorie = 0,
        int $genre = 0,
        string $periode = '0'
    ): Response
    {
        $videos = $videoRepository
            ->getRandomVideos($categorie, $genre, $periode);

        $videoParameters = $this->getParameter('shufler_video');
        $playlist = [];
        $i = 0;

        foreach ($videos as $video) {
            $api = $shuflerExtension->getPlatform($video->getLien());

            if ($shuflerExtension::YOUTUBE === $api && $vid = $shuflerExtension->getIdentifer($video->getLien(), $api)) {
                $playlist[] = $vid;
                $i ++;
            }

            if ($i >= $videoParameters['max_list_couch']) {
                break;
            }
        }

        return $this->render('video/couch.html.twig', [
            'videos' => $playlist
        ]);
    }
}
