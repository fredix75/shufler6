<?php

namespace App\Controller\Frixtur;

use App\Entity\Frixtur\Painter;
use App\Form\PainterType;
use App\Repository\Frixtur\PainterRepository;
use App\Repository\Frixtur\PaintingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Pagination\PaginatorInterface;

#[Route('', name: 'frixtur', host: '%host_frixtur%')]
#[IsGranted("ROLE_ADMIN")]
final class FrixturController extends AbstractController
{
    #[Route('/', name: '_home', methods: ['GET'])]
    public function home(PaintingRepository $paintingRepository): Response {
        $paintings = $paintingRepository->getRandomPaintings();

        return $this->render('frixtur/home.html.twig', [
            'paintings' => $paintings,
        ]);
    }

    #[Route('/list/{periode}/{page}', name: '_list', requirements: ['periode' => '\d{2}|0', 'page' => '\d+'], defaults: ['periode' => 0, 'page' => 1], methods: ['GET'])]
    public function index(PainterRepository $painterRepository, Request $request, PaginatorInterface $paginator, int $periode): Response
    {
        $limit = 25;
        $sort = $request->query->get('sort');
        $order = strtoupper($request->query->get('order')) === 'DESC' ? \SortDirection::Descending : \SortDirection::Ascending;

        if ($sort) {
            if ($periode) {
                $artists = $paginator
                    ->query($painterRepository->getPaintersAndPaintingsByPeriode($periode, $order, $sort))
                    ->perPage($limit)
                    ->throwOnOutOfRange()
                    ->paginate();

            } else {
                $artists = $paginator
                    ->query($painterRepository->getPaintersAndPaintings($order, $sort))
                    ->perPage($limit)
                    ->throwOnOutOfRange()
                    ->paginate();
            }
        } else {
            if ($periode) {
                $artists = $painterRepository->getPaintersAndPaintingsByPeriode($periode)->getQuery()->getResult();
            } else {
                $artists = $painterRepository->getPaintersAndPaintings()->getQuery()->getResult();
            }
            shuffle($artists);
            $artists = array_slice($artists, 0, $limit);
        }

        return $this->render('frixtur/artists_list.html.twig', [
            'artists' => $artists,
            'route_params' => [
                'sort' => $sort,
                'order' => strtoupper($request->query->get('order') ?? 'ASC'),
            ],
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

            return $this->redirectToRoute('frixtur_artist', [
                'id' => $painter->getId(),
            ]);
        }

        return $this->render('frixtur/artist.html.twig', [
            'artist' => $painter,
            'form' => $form
        ]);
    }

    #[Route('/search', name: '_search', methods: ['POST'])]
    public function searchPainter(Request $request): Response
    {
        $id = $request->request->all()['search_painter']['painter'];

        return $this->redirectToRoute('frixtur_artist', [
            'id' => $id,
        ]);
    }

    #[Route('/themes', name: '_themes', methods: ['GET'])]
    public function themes(EntityManagerInterface $em): Response
    {
        $themes = $em->getRepository(Painter::class)->findBy(['type' => 'THEM']);

        return $this->render('frixtur/themes.html.twig', [
            'themes' => $themes,
        ]);
    }

    #[Route('/random', name: '_random', methods: ['GET'])]
    public function painterRandom(PainterRepository $painterRepository): Response
    {
        $offset = rand(1,1020);
        $painter = $painterRepository->findBy([], null, 1, $offset)[0];

        return $this->redirectToRoute('frixtur_artist', ['id' => $painter->getId()]);
    }
}
