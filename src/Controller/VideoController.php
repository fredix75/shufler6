<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use App\Repository\VideoRepository;
use App\Twig\ShuflerExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function PHPUnit\Framework\throwException;

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

    #[Route('/search', name: 'search')]
    public function searchAction(Request $request, VideoRepository $videoRepository, int $page = 1)
    {
        $search = $request->get('search_field');

        $videos = $videoRepository->searchVideos($search, $page, $this->getParameter('shufler_video')['max_list']);
        $videos_count = count($videos);
        $pagination = [
            'search_field' => $search,
            'page' => $page,
            'route' => 'video_search',
            'pages_count' => ceil($videos_count / $this->getParameter('shufler_video')['max_list']),
            'route_params' => [
                'search_field' => $search
            ]
        ];

        return $this->render('video/list.html.twig', [
            'search' => $search,
            'pagination' => $pagination,
            'videos_count' => $videos_count,
            'videos' => $videos
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

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    #[Security("is_granted('ROLE_AUTEUR')")]
    public function editAction(
        Request $request,
        VideoRepository $videoRepository,
        Video $video = null
    ): Response
    {
        if (0 != $request->get('id') && !$video) {
            $this->addFlash('danger', 'No Way !!');
            return $this->redirectToRoute('video_list');
        }

        $video = $video ?? new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $video = $form->getData();
            $videoRepository->save($video, true);
            $this->addFlash('success', 'Video enregistrée');

            return $this->redirectToRoute('video_list');
        }

        return $this->render('video/edit.html.twig', [
            'form'   => $form,
            'video'  => $video,
            'periods'=> $this->getParameter('shufler_video')['periods']
        ]);
    }

    #[Route('/view/{id}', name: 'view', requirements: ['id' => '\d+'])]
    public function viewAction(Video $video): Response
    {
        return $this->render('video/view.html.twig', array(
            'video' => $video
        ));
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    #[Security("is_granted('ROLE_AUTEUR')")]
    public function deleteAction(VideoRepository $videoRepository, Video $video): Response
    {
        $videoRepository->remove($video, true);
        $this->addFlash('success', 'Video supprimée');
        return $this->redirectToRoute('video_list');
    }

    #[Route('/getVideoInfos/{plateforme}/{videoKey}', name: 'getVideoInfos')]
    public function getVideoInfos(
        HttpClientInterface $httpClient,
        string $plateforme,
        string $videoKey
    ): Response
    {
        if ('youtube' === $plateforme) {
            $response = $httpClient->request('GET', 'https://www.googleapis.com/youtube/v3/videos', [
                'query' => [
                    'key'  => $this->getParameter('youtube_key'),
                    'id'   => $videoKey,
                    'part' => 'snippet'
                ],
                'headers' => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ]
            ]);

            $result = json_decode($response->getContent(), true)['items'][0]['snippet'] ?? null;
        } elseif ('vimeo' === $plateforme) {
            if (!@file_get_contents('https://vimeo.com/api/v2/video/'.$videoKey.'.json')) {
                return new Response('No data', 404);
            }

            $response = $httpClient->request('GET', 'https://vimeo.com/api/v2/video/'.$videoKey.'.json', [
                'headers' => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ]
            ]);

            $result = json_decode($response->getContent(), true)[0] ?? null;
        }

        if ($response && 200 === $response->getStatusCode()) {
            Return new Response(json_encode($result));
        }

        return new Response('No data', 401);
    }
}
