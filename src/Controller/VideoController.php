<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\FilterVideosFormType;
use App\Form\VideoFormType;
use App\Helper\ApiRequester;
use App\Helper\VideoHelper;
use App\Repository\MusicCollection\TrackRepository;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route('/video', name: 'video')]
#[IsGranted("ROLE_USER")]
class VideoController extends AbstractController
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    #[Route('/list/{categorie}/{genre}/{periode}/{page}',
        name: '_list',
        requirements: ['categorie' => '\d+', 'genre' => '\d+|-\d+', 'page' => '\d+'],
        defaults: ['categorie' => 0, 'genre' => 0, 'periode' => '0', 'page' => 1]
    )]
    public function list(
        Request         $request,
        VideoRepository $videoRepository,
        int             $categorie,
        int             $genre,
        string          $periode,
        int             $page
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

    #[Route('/couch/{categorie}/{genre}/{periode}',
        name: '_couch',
        requirements: ['categorie' => '\d+', 'genre' => '\d+|-\d+'],
        defaults: ['categorie' => 0, 'genre' => 0, 'periode' => '0']
    )]
    public function couch(
        Request         $request,
        VideoRepository $videoRepository,
        VideoHelper     $videoHelper,
        ?int             $categorie,
        ?int             $genre,
        string          $periode
    ): Response
    {
        $categorie = (int)$request->query->get('categorie') ?? $categorie;
        $genre = (int)$request->query->get('genre') ?? $genre;
        $periode = $request->query->get('periode') ?? $periode;
        $search = $request->query->get('search') ?? null;

        $form = $this->createForm(FilterVideosFormType::class, [
            'categorie' => $categorie,
            'genre' => $genre,
            'periode' => $periode,
            'search' => $search,
        ]);

        $videos = $videoRepository->getRandomVideos($search, $categorie, $genre, $periode, 'youtube');
        $videoParameters = $this->getParameter('shufler_video');

        $trackIntro = [
            'titre' => 'Intro',
            'auteur' => '',
            'annee' => null,
            'youtubeKey' => $videoParameters['intro_couch']
        ];

        $list = [$trackIntro];
        $playlist = [$trackIntro['youtubeKey']];

        $i = 0;
        foreach ($videos as $video) {
            $video = $this->serializer->normalize($video);

            if (!\in_array($videoHelper->getIdentifer($video['lien'], 'youtube'), $playlist)) {
                $video['youtubeKey'] = $videoHelper->getIdentifer($video['lien'], 'youtube');
                $list[] = $video;
                $playlist[] = $videoHelper->getIdentifer($video['lien'], 'youtube');
                $i++;
            }
            if ($i >= $videoParameters['max_list_couch']) {
                break;
            }
        }

        return $this->render('video/couch.html.twig', [
            'list' => $list,
            'videos' => $playlist,
            'form_video' => $form,
        ]);
    }

    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'], defaults: ['id' => 0])]
    #[isGranted('VIDEO_EDIT', "video", 'No pasaran')]
    public function edit(
        Request         $request,
        VideoRepository $videoRepository,
        ?Video          $video
    ): Response
    {
        $video = $video ?? new Video();

        if ($request->get('videokey')) {
            $video->setLien($request->get('videokey'));
        }

        $form = $this->createForm(VideoFormType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $videoRepository->save($video, true);
            $this->addFlash('success', 'Vidéo enregistrée');

            return $this->redirectToRoute('video_view', [
                'id' => $video->getId(),
            ]);
        }

        return $this->render('video/edit.html.twig', [
            'form' => $form,
            'video' => $video,
            'periods' => $this->getParameter('shufler_video')['periods'],
        ]);
    }

    #[Route('/view/{id}', name: '_view', requirements: ['id' => '\d+'])]
    public function view(Video $video): Response
    {
        return $this->render('video/view.html.twig', [
            'video' => $video
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('VIDEO_DELETE', "video", "No pasaran")]
    public function delete(Request $request, VideoRepository $videoRepository, Video $video): Response
    {
        if ($this->isCsrfTokenValid('video_delete'.$video->getId(), $request->get('_token'))) {
            $videoRepository->remove($video, true);
            $this->addFlash('success', 'Vidéo supprimée');
        }
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
        ApiRequester $apiRequester,
        string       $plateforme,
        string       $videoKey
    ): Response
    {
        $result = [];
        if ('youtube' === $plateforme) {
            $response = $apiRequester->sendRequest(VideoHelper::YOUTUBE, '/videos', [
                'id' => $videoKey,
                'part' => 'snippet'
            ]);

            $result = json_decode($response->getContent(), true)['items'][0]['snippet'] ?? null;
        } elseif ('vimeo' === $plateforme) {
            if (!@file_get_contents($this->getParameter('vimeo_api_url') . '/video/' . $videoKey . '.json')) {
                return new Response('No data', Response::HTTP_NOT_FOUND);
            }

            $response = $apiRequester->sendRequest(VideoHelper::VIMEO, sprintf('/video/%s.json', $videoKey));

            $result = json_decode($response->getContent(), true)[0] ?? null;
        }

        if (!empty($response) && Response::HTTP_OK === $response->getStatusCode()) {
            return new Response(json_encode($result));
        }

        return new Response('No data', Response::HTTP_NOT_FOUND);
    }

    #[Route('/autocomplete', name: '_autocomplete')]
    public function autocomplete(Request $request, VideoRepository $videoRepository, TrackRepository $trackRepository): Response
    {
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('q');
            $videos = $videoRepository->searchAjax($search);
            $tracks = $trackRepository->searchAjax($search);
            $suggestions = [];
            if ($videos) {
                foreach ($videos as $video) {
                    $suggestions[] = [
                        'suggestion' => $video,
                        'class' => 'video'
                    ];
                }
            }

            if ($tracks) {
                foreach ($tracks as $track) {
                    if (!\in_array($track, $suggestions)) {
                        $suggestions[] = [
                            'suggestion' => $track,
                            'class' => 'track'
                        ];
                    }
                }
            }

            return $this->render('video/autocomplete.html.twig', [
                'suggestions' => $suggestions
            ]);
        }

        return new Response('error', Response::HTTP_PAYMENT_REQUIRED);
    }

    #[Route('/trash/{page}', name: '_trash', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    #[IsGranted('VIDEO_VIEW')]
    public function trash(
        Request         $request,
        VideoRepository $videoRepository,
        int             $page
    ): Response
    {
        $videos = $videoRepository->getPaginatedTrash($page, $this->getParameter('shufler_video')['max_list']);
        $pagination = [
            'page' => $page,
            'route' => 'video_trash',
            'pages_count' => ceil(count($videos) / $this->getParameter('shufler_video')['max_list']),
            'route_params' => $request->attributes->get('_route_params'),
        ];

        return $this->render('video/trash.html.twig', [
            'videos' => $videos,
            'trash' => true,
            'pagination' => $pagination,
        ]);
    }
}
