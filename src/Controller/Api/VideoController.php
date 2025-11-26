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
        $categorie = $request->query->get('categorie') ?? null;
        $genre = $request->query->get('genre') ?? null;
        $periode = $request->query->get('periode') ?? '0';
        $page = $request->query->get('page') ?? 1;
        $maxItems = $this->getParameter('shufler_video')['api_max_list'];

        return $videoRepository->getPaginatedVideos($categorie, $genre, $periode, $page, $maxItems);
    }

}
