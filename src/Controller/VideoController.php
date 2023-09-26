<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoFormType;
use App\Helper\VideoHelper;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/video', name: 'video')]
#[IsGranted("ROLE_USER")]
class VideoController extends AbstractController
{
    #[Route('/list/{categorie}/{genre}/{periode}/{page}', name: '_list', requirements: ['categorie' => '\d+', 'genre' => '\d+|-\d+', 'page' => '\d+'])]
    public function list(
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

        $pagination = [
            'page' => $page,
            'route' => 'video_list',
            'pages_count' => (int)ceil(count($videos) / $videoParameters['max_list']),
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

    #[Route('/search/{page}', name: '_search', requirements: ['id' => '\d+'])]
    public function search(Request $request, VideoRepository $videoRepository, int $page = 1)
    {
        $search = $request->get('search_field');

        $videos = $search ? $videoRepository->searchVideos($search, $page, $this->getParameter('shufler_video')['max_list']) : [];
        $videosCount = count($videos);
        $pagination = [
            'search_field' => $search,
            'page' => $page,
            'route' => 'video_search',
            'pages_count' => ceil($videosCount / $this->getParameter('shufler_video')['max_list']),
            'route_params' => [
                'search_field' => $search
            ]
        ];

        return $this->render('video/list.html.twig', [
            'search' => $search,
            'pagination' => $pagination,
            'videos_count' => $videosCount,
            'videos' => $videos
        ]);
    }

    #[Route('/couch/{categorie}/{genre}/{periode}', name: '_couch', requirements: ['categorie' => '\d+', 'genre' => '\d+|-\d+'])]
    public function couch(
        VideoRepository $videoRepository,
        VideoHelper $videoHelper,
        int $categorie = 0,
        int $genre = 0,
        string $periode = '0'
    ): Response
    {
        $videos = $videoRepository->getRandomVideos($categorie, $genre, $periode, 'youtube');
        $videoParameters = $this->getParameter('shufler_video');
        $playlist = [$videoParameters['intro_couch']];
        $i = 0;
        foreach ($videos as $video) {
            $playlist[] = $videoHelper->getIdentifer($video->getLien(), 'youtube.com');
            if ($i >= $videoParameters['max_list_couch']) {
                break;
            }
            $i++;
        }

        return $this->render('video/couch.html.twig', [
            'videos' => $playlist
        ]);
    }

    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'])]
    #[isGranted('VIDEO_EDIT', "video", 'No pasaran')]
    public function edit(
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

        if ($request->get('videokey')) {
            $video->setLien($request->get('videokey'));
        }

        $form = $this->createForm(VideoFormType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $video = $form->getData();
            $videoRepository->save($video, true);
            $this->addFlash('success', 'Vidéo enregistrée');

            return $this->redirectToRoute('video_view', [
                'id' => $video->getId(),
            ]);
        }

        return $this->render('video/edit.html.twig', [
            'form'   => $form,
            'video'  => $video,
            'periods'=> $this->getParameter('shufler_video')['periods'],
        ]);
    }

    #[Route('/view/{id}', name: '_view', requirements: ['id' => '\d+'])]
    public function view(Video $video): Response
    {
        return $this->render('video/view.html.twig', [
            'video' => $video
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('VIDEO_DELETE', "video", "No pasaran")]
    public function delete(VideoRepository $videoRepository, Video $video): Response
    {
        $videoRepository->remove($video, true);
        $this->addFlash('success', 'Vidéo supprimée');

        return $this->redirectToRoute('video_list');
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    #[Route('/getVideoInfos/{plateforme}/{videoKey}', name: '_getVideoInfos')]
    public function getVideoInfos(
        HttpClientInterface $httpClient,
        string $plateforme,
        string $videoKey
    ): Response
    {
        if ('youtube' === $plateforme) {
            $response = $httpClient->request('GET', sprintf('%s/videos', $this->getParameter('youtube_api_url')), [
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
            if (!@file_get_contents($this->getParameter('vimeo_api_url').'/video/'.$videoKey.'.json')) {
                return new Response('No data', Response::HTTP_NOT_FOUND);
            }

            $response = $httpClient->request('GET', sprintf('%s/video/%s.json', $this->getParameter('vimeo_api_url'), $videoKey), [
                'headers' => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ]
            ]);

            $result = json_decode($response->getContent(), true)[0] ?? null;
        }

        if (!empty($response) && Response::HTTP_OK === $response->getStatusCode()) {
            Return new Response(json_encode($result));
        }

        return new Response('No data', Response::HTTP_NOT_FOUND);
    }

    #[Route('/autocomplete', name: '_autocomplete')]
    public function autocomplete(Request $request, VideoRepository $videoRepository): Response
    {
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('q');
            $videos = $videoRepository->searchAjax($search);
            $suggestions = [];
            $suggestions['suggestions'] = [];

            if ($videos) {
                foreach ($videos as $video) {
                    $suggestions['suggestions'][] = $video;
                }
            }

            return $this->render('video/autocomplete.html.twig', [
                'suggestions' => $suggestions['suggestions']
            ]);
        }

        return new Response('error', Response::HTTP_PAYMENT_REQUIRED);
    }

    #[Route('/trash/{page}', name: '_trash', requirements: ['page' => '\d+'])]
    #[IsGranted('VIDEO_VIEW')]
    public function trash(
        Request $request,
        VideoRepository $videoRepository,
        int $page = 1
    ): Response
    {
        $videos = $videoRepository->getPaginatedTrash($page, $this->getParameter('shufler_video')['max_list']);
        $pagination = [
            'page' => $page,
            'route' => 'video_trash',
            'pages_count' => ceil(count($videos) / $this->getParameter('shufler_video')['max_list']),
            'route_params' => $request->attributes->get('_route_params'),
        ];

        return $this->render('video/list.html.twig', [
            'videos' => $videos,
            'trash'  => true,
            'pagination' => $pagination,
        ]);
    }
}
