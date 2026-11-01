<?php

namespace App\Controller;

use App\Form\FilterVTrackType;
use App\Helper\MusicHelper;
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
    #[Route('/', name: '_index')]
    public function index(VTrackRepository $vTrackRepository): Response
    {
        $tracks = $vTrackRepository->createQueryBuilder('vt')
            ->where('vt.dataOrigin = :origin')
            ->andWhere('vt.youtubeKey IS NOT NULL')
            ->setParameter('origin', 'radial')
            ->getQuery()->getResult();

        shuffle($tracks);

        $tracks = array_slice($tracks, 0, 100);

        return $this->render('radial/index.html.twig', [
            'tracks' => $tracks,
        ]);
    }

    #[Route('/couch', name: '_couch')]
    public function couch(Request $request, VTrackRepository $vTrackRepository, MusicHelper $musicHelper): Response
    {
        $params = $musicHelper->handleParams($request);
        $form = $this->createForm(FilterVTrackType::class, $params, [
            'mode' => 'radial',
        ]);

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
