<?php

namespace App\Controller;

use App\Repository\MoodRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mood', name: 'mood')]
class MoodController extends AbstractController
{
    #[Route('/get/', name: '_get')]
    public function get(Request $request, MoodRepository $moodRepository): Response
    {
        return $this->json($moodRepository->search($request->get('q')));
    }
}
