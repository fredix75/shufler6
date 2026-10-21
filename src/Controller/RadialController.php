<?php

namespace App\Controller;

use App\Repository\RadialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/radial', name: 'radial')]
final class RadialController extends AbstractController
{

    #[Route('/', name: '_index')]
    public function index(RadialRepository $radialRepository): Response
    {
        $tracks = $radialRepository->createQueryBuilder('r')
            ->where('r.youtubeKey IS NOT NULL')
            ->getQuery()->getResult();

        shuffle($tracks);

        $tracks = array_slice($tracks, 0, 100);

        return $this->render('radial/index.html.twig', [
            'tracks'=> $tracks,
        ]);
    }
}
