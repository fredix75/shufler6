<?php

namespace App\Controller;

use App\Repository\FluxRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WeshtavuController extends AbstractController
{
    #[Route('/weshtavu', name: 'app_weshtavu')]
    public function index(FluxRepository $fluxRepository): Response
    {
        $playlist = $fluxRepository->findOneBy(['name' => 'Vu - France 2']);

        return $this->render('weshtavu/index.html.twig', [
            'playlist' => $playlist,
        ]);
    }
}
