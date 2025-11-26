<?php

namespace App\Controller\Api;


use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RandomVideoController extends AbstractController
{
    public function __invoke(Request $request, VideoRepository $videoRepository): array
    {
        $categorie = $request->query->get('categorie');
// en test
        //return array_slice($videoRepository->getRandomVideos(null, $categorie),0,12);
        return [$videoRepository->find(1)];
    }

}
