<?php

namespace App\Controller\Api;


use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class RandomVideoController extends AbstractController
{
    public function __invoke(Request $request, VideoRepository $videoRepository, #[MapQueryParameter] ?int $categorie): array
    {
// en test
        //return array_slice($videoRepository->getRandomVideos(null, $categorie),0,12);
        return [$videoRepository->find(1)];
    }

}
