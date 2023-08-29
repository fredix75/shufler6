<?php

namespace App\Controller;

use App\Helper\FileHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/download', name: 'download')]
#[IsGranted("ROLE_ADMIN")]
class DownloadController extends AbstractController
{

    #[Route('/resource', name: '_resource')]
    public function downloadResource(Request $request, FileHelper $fileHelper): JsonResponse
    {
        $url = $request->get('url');
        $directory = $this->getParameter('resources')['downloads'];
        $filePath = $fileHelper->copyFileFromUrl($url, $directory);

        return new JsonResponse($filePath);
    }
}
