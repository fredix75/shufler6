<?php

namespace App\Controller;

use App\Entity\MusicCollection\CloudTrack;
use App\Form\CloudTrackFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/music/cloudtrack', name: 'music_cloudtrack')]
class CloudTrackController extends AbstractController
{
    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'], defaults: ['id' => 0])]
    public function editCloudTrack(?CloudTrack $cloudTrack, Request $request, EntityManagerInterface $em): Response
    {
        $cloudTrack = $cloudTrack ?? new CloudTrack();

        if ($request->get('trackkey')) {
            $cloudTrack->setYoutubeKey($request->get('trackkey'));
            $em->persist($cloudTrack);
            $em->flush();

            return $this->redirectToRoute('music_all', ['mode' => 'tracks']);
        }

        $form = $this->createForm(CloudTrackFormType::class, $cloudTrack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $youtubeKey = $form->get('youtubeKey')->getData();
            $cloudTrack->setYoutubeKey($youtubeKey);
            $em->persist($cloudTrack);
            $em->flush();

            $this->addFlash('success', 'Un morceau a été sauvegardé');

            return $this->redirectToRoute('music_cloud-all', ['mode' => 'tracks']);
        }

        return $this->render('music/cloud-track_edit.html.twig', [
            'form' => $form,
            'cloudTrack' => $cloudTrack,
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function deleteCloudTrack(CloudTrack $cloudTrack, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cloudTrack->getId(), $request->get('_token'))) {
            $em->remove($cloudTrack);
            $em->flush();
            $this->addFlash('success', 'Une track a été supprimée');
            return $this->redirectToRoute('music_cloud-all', ['mode' => 'tracks']);
        }

        throw $this->createAccessDeniedException();
    }
}
