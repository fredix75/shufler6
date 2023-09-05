<?php

namespace App\Controller\Api;


use App\Repository\VideoRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class VideoController extends AbstractController
{
    public function __invoke(Request $request, VideoRepository $videoRepository): Paginator
    {
        $categorie = $request->get('categorie') ?? null;
        $genre = $request->get('genre') ?? null;
        $periode = $request->get('periode') ?? '0';
        $page = $request->get('page') ?? 1;
        $maxItems = $this->getParameter('shufler_video')['api_max_list'];

        return $videoRepository->getPaginatedVideos($categorie, $genre, $periode, $page, $maxItems);
    }

}