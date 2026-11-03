<?php

namespace App\Controller;

use App\Form\FilterVTrackType;
use App\Helper\MusicHelper;
use App\Repository\FilterPieceRepository;
use App\Repository\MusicCollection\VTrackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/radial', name: 'radial')]
#[IsGranted("ROLE_ADMIN")]
final class RadialController extends AbstractController
{
    #[Route('/{page}', name: '_index', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    public function index(Request $request, VTrackRepository $vTrackRepository, int $page): Response
    {
        $p = $request->query->all();
        $params = [
            'auteur' => $p['auteur'] ?? null,
            'album' => $p['album'] ?? null,
            'annee' => $p['annee'] ?? null,
            'search' => $p['search'] ?? null,
            'random' => !empty($p['random']) && $p['random'] === '1',
            'hasYoutubeKey' => true,
            'dataOrigin' => 'radial',
        ];

        $form = $this->createForm(FilterVTrackType::class, $params, ['mode' => 'radial']);
        $form->handleRequest($request);
        $max = $this->getParameter('radial')['max_nb_tracks'];
        $offset = ($page - 1) * $max;

        $tracks = $vTrackRepository->getVTracks($params, $offset, $max);

        $routeParams = $request->attributes->get('_route_params');
        if (isset($p['page'])) {
            $page = 1;
            $routeParams['page'] = 1;
        }

        if (false === $params['random']) {
            $pagination = [
                'page' => $page,
                'route' => 'radial_index',
                'pages_count' => (int)ceil(count($tracks) / $max),
                'route_params' => array_merge($routeParams, $params)
            ];
        } else {
            $tracks = array_slice($tracks, 0, $max);
            shuffle($tracks);
        }

        return $this->render('radial/index.html.twig', [
            'tracks' => $tracks,
            'form_track' => $form,
            'pagination' => $pagination ?? [],
        ]);
    }

    #[Route('/filter-couch', name: '_filter_couch', methods: ['GET'])]
    public function filterCouch(Request $request, MusicHelper $musicHelper): Response
    {
        $params = $musicHelper->handleParams($request);
        $form = $this->createForm(FilterVTrackType::class, $params, ['mode' => 'radial']);

        return $this->render('radial/filter_couch.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/couch', name: '_couch')]
    public function couch(Request $request, VTrackRepository $vTrackRepository, MusicHelper $musicHelper): Response
    {
        $params = $musicHelper->handleParams($request);
        $form = $this->createForm(FilterVTrackType::class, $params, [
            'mode' => 'radial',
        ]);
        $params['dataOrigin'] = 'radial';
        $pieces = $vTrackRepository->getVTracks($params);

        shuffle($pieces);

        list($list, $playlist) = $musicHelper->buildPlaylist($pieces);

        return $this->render('video/couch.html.twig', [
            'list' => $list,
            'videos' => $playlist,
            'form_track' => $form,
        ]);
    }
}
