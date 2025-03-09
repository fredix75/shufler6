<?php

namespace App\Controller;

use App\Helper\FileHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/resource', name: 'resource')]
#[IsGranted("ROLE_ADMIN")]
class ResourceController extends AbstractController
{

    /**
     * @throws \Exception
     */
    #[Route('/download', name: '_download')]
    public function downloadResource(Request $request, FileHelper $fileHelper, SluggerInterface $slugger): JsonResponse
    {
        $url = $request->get('url');
        $title = strtolower($slugger->slug($request->get('title')));
        $directory = $this->getParameter('resources')['downloads'];
        $filePath = $fileHelper->copyFileFromUrl($url, $directory, $title);

        return new JsonResponse($filePath);
    }

    #[Route('/delete', name: '_delete')]
    public function deleteResources(Request $request, FileHelper $fileHelper): JsonResponse
    {
        $file = $request->get('file');
        $directory = $this->getParameter('resources')['downloads'];
        $fileHelper->deleteFile($directory . '/' . $file);
        return new JsonResponse('ok', 204);
    }
}
