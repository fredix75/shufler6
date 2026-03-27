<?php

namespace App\Controller;

use App\Entity\PictureCollection\Painter;
use App\Entity\PictureCollection\Painting;
use App\Form\PainterType;
use App\Repository\Painting\PainterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('picture', name: 'picture')]
#[IsGranted("ROLE_ADMIN")]
final class PictureController extends AbstractController
{

    #[Route('/', name: '_home', methods: ['GET'])]
    public function home(EntityManagerInterface $em): Response {
        $paintings = $em->getRepository(Painting::class)->getRandomPaintings();

        return $this->render('picture/home.html.twig', [
            'paintings' => $paintings,
        ]);
    }

    #[Route('/list/{periode}/{page}', name: '_list', requirements: ['periode' => '\d{2}|0', 'page' => '\d+'], defaults: ['periode' => 0, 'page' => 1], methods: ['GET'])]
    public function index(int $periode, int $page, PainterRepository $painterRepository, Request $request): Response
    {
        $limit = 25;

        $offset = ($page - 1) * $limit;
        $pagination = [];
        $sort = $request->query->get('sort');
        $order = $request->query->get('order') ?? 'ASC';

        if ($sort) {
            if ($periode) {
                list($nbArtists, $artists) = $painterRepository->getPaintersAndPaintingsByPeriode($periode, $limit, $offset, $order, $sort);
            } else {
                $nbArtists = $painterRepository->count(['type' => null]);
                $artists = $painterRepository->getPaintersAndPaintings($limit, $offset, $order, $sort);
            }
            $pagination = [
                'page' => $page,
                'route' => 'picture_list',
                'pages_count' => (int)ceil($nbArtists / 25),
                'route_params' => array_merge($request->attributes->get('_route_params'),[
                    'sort' => $sort,
                    'order' => strtoupper($order),
                ]),
            ];
        } else {
            if ($periode) {
                $artists = $painterRepository->getPaintersAndPaintingsByPeriode($periode, $limit);
            } else {
                $artists = $painterRepository->getPaintersAndPaintings($limit);
            }
            shuffle($artists);
            $artists = array_slice($artists, 0, $limit);
        }

        return $this->render('picture/artists_list.html.twig', [
            'artists' => $artists,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/artist/{id}', name: '_artist', requirements: ['id' => '\d+'])]
    public function artist(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $painter = $em->getRepository(Painter::class)->getPainterAndPaintings($id);

        $form = $this->createForm(PainterType::class, $painter);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($painter);
            $em->flush();

            $this->addFlash('success', 'painter updated');

            return $this->redirectToRoute('picture_artist', [
                'id' => $painter->getId(),
            ]);
        }

        return $this->render('picture/artist.html.twig', [
            'artist' => $painter,
            'form' => $form
        ]);
    }

    #[Route('/search', name: '_search', methods: ['POST'])]
    public function searchPainter(Request $request): Response
    {
        $id = $request->request->all()['search_painter']['painter'];

        return $this->redirectToRoute('picture_artist', [
            'id' => $id,
        ]);
    }

    #[Route('/themes', name: '_themes', methods: ['GET'])]
    public function themes(EntityManagerInterface $em): Response
    {
        $themes = $em->getRepository(Painter::class)->findBy(['type' => 'THEM']);

        return $this->render('picture/themes.html.twig', [
            'themes' => $themes,
        ]);
    }

    #[Route('/random', name: '_random', methods: ['GET'])]
    public function painterRandom(PainterRepository $painterRepository): Response
    {
        $offset = rand(1,1020);
        $painter = $painterRepository->findBy([], null, 1, $offset)[0];

        return $this->redirectToRoute('picture_artist', ['id' => $painter->getId()]);
    }
}
