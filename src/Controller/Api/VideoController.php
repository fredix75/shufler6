<?php

namespace App\Controller\Api;


use ApiPlatform\Doctrine\Orm\Paginator;
use App\Dto\VideoQuery;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\UX\Pagination\PaginatorInterface;

class VideoController extends AbstractController
{
    public function __invoke(VideoRepository $videoRepository, PaginatorInterface $paginator, #[MapQueryString] VideoQuery $query): array
    {
        $categorie = $query->categorie;
        $genre = $query->genre;
        $periode = $query->periode;
        $maxItems = $this->getParameter('shufler_video')['api_max_list'];

        return $paginator->query($videoRepository->getVideosQuery($categorie, $genre, $periode)->orderBy('v.id', 'ASC'))
            ->perPage($maxItems)
            ->throwOnOutOfRange()
            ->paginate()->getItems();
    }
}
