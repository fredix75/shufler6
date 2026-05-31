<?php

namespace App\Controller;

use App\Helper\EventHelper;
use App\Repository\EventRepository;
use App\Repository\FilmRepository;
use App\Repository\FluxRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WeshtavuController extends AbstractController
{
    #[Route('/weshtavu/{index}', name: 'app_weshtavu', requirements: ['index' => '\d+'], methods: ['GET'], defaults: ['index' => 1750])]
    public function index(int $index, FluxRepository $fluxRepository, EventRepository $eventRepository, EventHelper $eventHelper, FilmRepository $filmRepository): Response
    {
        list($firstDate, $endDate) = $eventHelper->getDateIntervall($index);
        $events = $eventRepository->findByDateIntervall($firstDate, $endDate);
        $films = $filmRepository->findByDateIntervall($firstDate, $endDate);

        $playlist = $fluxRepository->findOneBy(['name' => 'Vu - France 2']);

        return $this->render('weshtavu/index.html.twig', [
            'playlist' => $playlist,
            'events' => $events,
            'films' => $films,
            'date' => $endDate,
        ]);
    }
}
