<?php

namespace App\Controller;

use App\Entity\MusicCollection\Album;
use App\Entity\MusicCollection\CloudAlbum;
use App\Entity\MusicCollection\CloudTrack;
use App\Form\AlbumFormType;
use App\Form\CloudAlbumFormType;
use App\Form\FilterTracksFormType;
use App\Helper\ApiRequester;
use App\Repository\MusicCollection\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/music/album', name: 'music_album')]
#[IsGranted('ROLE_ADMIN')]
class AlbumController extends AbstractController
{
    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'])]
    public function editAlbum(Album $album, Request $request, AlbumRepository $albumRepository): Response
    {
        if ($request->get('albumkey')) {
            $album->setYoutubeKey($request->get('albumkey'));
            $albumRepository->save($album, true);

            return $this->redirectToRoute('music_albums');
        }

        if ($request->get('albumpicture')) {
            $album->setPicture($request->get('albumpicture'));
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

    #[Route('/cloud/edit/{id}', name: '_cloud_edit',requirements: ['id' => '\d+'], defaults: ['id' => null])]
    public function edit(?CloudAlbum $album, Request $request, EntityManagerInterface $em, ApiRequester $apiRequester): Response
    {
        $album = $album ?? new CloudAlbum();
        if ($request->get('cloudalbumkey')) {
            $album->setYoutubeKey($request->get('cloudalbumkey'));
        }
        $form = $this->createForm(CloudAlbumFormType::class, $album);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (empty($album->getPicture())) {
                try {
                    $response = $apiRequester->sendRequest('last_fm', '', [
                        'artist'   => $album->getAuteur(),
                        'album'   => $album->getName(),
                        'method' => 'album.getInfo',
                    ]);
                    $response = json_decode($response->getContent(), true) ?? [];
                    $album->setPicture($response['album'] ? $response['album']['image'][4]['#text'] : '');
                } catch(\Exception $e) {
                    // pas grave, on fait rien
                }
            }
            $em->persist($album);
            $em->flush();
            $this->addFlash('success', 'Un album a été crée');
            return $this->redirectToRoute('music_cloud-all', ['mode' => 'albums']);
        }

        return $this->render('music/cloud-album_edit.html.twig', [
            'form' => $form,
            'cloudAlbum' => $album,
        ]);
    }

    #[Route('/cloud/delete/{id}', name: '_cloud_delete', requirements: ['id' => '\d+'])]
    public function deleteCloudTrack(CloudAlbum $cloudAlbum, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cloudAlbum->getId(), $request->get('_token'))) {
            $em->remove($cloudAlbum);
            $em->flush();
            $this->addFlash('success', 'Un album a été supprimée');
            return $this->redirectToRoute('music_cloud-all', ['mode' => 'albums']);
        }

        throw $this->createAccessDeniedException();
    }

    #[Route('s/{page}', name: 's', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    public function getAlbums(Request $request, AlbumRepository $albumRepository, int $page): Response
    {
        $params = [
            'auteur' => $request->get('auteur') ?? null,
            'album' => $request->get('album') ?? null,
            'genres' => $request->get('genres') ?? null,
            'annee' => $request->get('annee') ?? null,
            'search' => $request->get('search') ?? null,
            'random' => $request->get('random') === '1',
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
}
