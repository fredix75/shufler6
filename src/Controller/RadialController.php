<?php

namespace App\Controller;

use App\Dto\MusicQuery;
use App\Form\FilterVTrackType;
use App\Helper\MusicHelper;
use App\Repository\MusicCollection\VTrackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Pagination\PaginatorInterface;

#[Route('/radial', name: 'radial')]
#[IsGranted("ROLE_ADMIN")]
final class RadialController extends AbstractController
{

    public function __construct(private readonly PaginatorInterface $paginator) {}

    #[Route('/{page}', name: '_index', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    public function index(#[MapQueryString] MusicQuery $query, Request $request, VTrackRepository $vTrackRepository, MusicHelper $musicHelper): Response
    {
        $params = $musicHelper->handleParams($query);

        $params['dataOrigin'] = 'radial';

        $form = $this->createForm(FilterVTrackType::class, $params, ['mode' => 'radial']);
        $form->handleRequest($request);
        $max = $this->getParameter('radial')['max_nb_tracks'];

        if (false === $params['random']) {
            $tracks = $this->paginator
                ->query($vTrackRepository->getVTracks($params))
                ->perPage($max)
                ->throwOnOutOfRange()
                ->paginate();
        } else {
            $tracks = $vTrackRepository->getVTracks($params)->getQuery()->getResult();
            shuffle($tracks);
            $tracks = array_slice($tracks, 0, $max);
        }

        return $this->render('radial/index.html.twig', [
            'tracks' => $tracks,
            'form_track' => $form,
        ]);
    }

    #[Route('/filter-couch', name: '_filter_couch', methods: ['GET'])]
    public function filterCouch(#[MapQueryString] MusicQuery $query, MusicHelper $musicHelper): Response
    {
        $params = $musicHelper->handleParams($query);
        $form = $this->createForm(FilterVTrackType::class, $params, ['mode' => 'radial']);

        return $this->render('radial/filter_couch.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/couch', name: '_couch')]
    public function couch(#[MapQueryString] MusicQuery $query, VTrackRepository $vTrackRepository, MusicHelper $musicHelper): Response
    {
        $params = $musicHelper->handleParams($query);
        $form = $this->createForm(FilterVTrackType::class, $params, [
            'mode' => 'radial',
        ]);
        $params['dataOrigin'] = 'radial';
        $pieces = $vTrackRepository->getVTracks($params)->getQuery()->getResult();

        shuffle($pieces);

        list($list, $playlist) = $musicHelper->buildPlaylist($pieces);

        return $this->render('video/couch.html.twig', [
            'list' => $list,
            'videos' => $playlist,
            'form_track' => $form,
        ]);
    }
}
