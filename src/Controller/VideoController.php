<?php

namespace App\Controller;

use App\Dto\VideoQuery;
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
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\UX\Pagination\PaginatorInterface;

#[Route('/video', name: 'video')]
#[IsGranted("ROLE_USER")]
final class VideoController extends AbstractController
{

    #[Route('/list/{page}',
        name: '_list',
        requirements: ['page' => '\d+'],
        defaults: ['page' => 1]
    )]
    public function list(
        VideoRepository $videoRepository,
        PaginatorInterface $paginator,
        #[MapQueryString] VideoQuery $query
    ): Response
    {
        $videoParameters = $this->getParameter('shufler_video');
        $videos = $paginator
            ->query($videoRepository->getVideosQuery($query->categorie, $query->genre, $query->periode))
            ->perPage($videoParameters['max_list'])
            ->throwOnOutOfRange()
            ->paginate();

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
        ]);
    }

    #[Route('/couch', name: '_couch')]
    public function couch(
        VideoRepository $videoRepository,
        VideoHelper     $videoHelper,
        #[MapQueryString] VideoQuery $query
    ): Response
    {
        $categorie = $query->categorie;
        $genre = $query->genre;
        $periode = $query->periode;
        $search = $query->search;

        $form = $this->createForm(FilterVideosFormType::class, [
            'categorie' => $categorie,
            'genre' => $genre,
            'periode' => $periode,
            'search' => $search,
        ]);

        $videos = $videoRepository->getRandomVideos($search, $categorie, $genre, $periode, 'youtube');

        list($list, $playlist) = $videoHelper->buildPlaylist($videos);

        return $this->render('video/couch.html.twig', [
            'list' => $list,
            'videos' => $playlist,
            'form_video' => $form,
        ]);
    }

    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'], defaults: ['id' => null])]
    #[isGranted('VIDEO_EDIT', "video", 'No pasaran')]
    public function edit(
        Request         $request,
        VideoRepository $videoRepository,
        ?Video          $video
    ): Response
    {
        if ($video && !empty($request->query->get('publish'))) {
            if ($request->query->get('publish') === '1') {
                $video->setPublished(true);
                $this->addFlash('success', 'Vidéo publiée');
            } else {
                $video->setPublished(false);
                $this->addFlash('success', 'Vidéo dépubliée');
            }
            $videoRepository->save($video, true);
            return $this->redirectToRoute('video_view', ['id' => $video->getId()]);
        }

        $video = $video ?? new Video();

        if ($request->request->get('videokey')) {
            $video->setLien($request->request->get('videokey'));
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
    public function delete(VideoRepository $videoRepository, Video $video, #[MapQueryParameter] string $_token): Response
    {
        if ($this->isCsrfTokenValid('video_delete'.$video->getId(), $_token)) {
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
    public function autocomplete(Request $request, VideoRepository $videoRepository, TrackRepository $trackRepository, #[MapQueryParameter] string $q): Response
    {
        if ($request->isXmlHttpRequest()) {
            $videos = $videoRepository->searchAjax($q);
            $tracks = $trackRepository->searchAjax($q);
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
        PaginatorInterface $paginator
    ): Response
    {
        $videos = $paginator->query($videoRepository->getTrashQuery())
                    ->perPage($this->getParameter('shufler_video')['max_list'])
                    ->throwOnOutOfRange()
                    ->paginate();

        return $this->render('video/trash.html.twig', [
            'videos' => $videos,
        ]);
    }
}
