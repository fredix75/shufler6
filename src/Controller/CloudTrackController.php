<?php

namespace App\Controller;

use App\Entity\MusicCollection\CloudTrack;
use App\Form\CloudTrackFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/music/cloudtrack', name: 'music_cloudtrack')]
#[IsGranted('ROLE_ADMIN')]
class CloudTrackController extends AbstractController
{
    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'], defaults: ['id' => null])]
    public function editCloudTrack(?CloudTrack $cloudTrack, Request $request, EntityManagerInterface $em): Response
    {
        $cloudTrack = $cloudTrack ?? new CloudTrack();

        if ($request->request->get('cloudtrackkey')) {
            $cloudTrack->setYoutubeKey($request->request->get('cloudtrackkey'));
            $em->persist($cloudTrack);
            $em->flush();

            $this->addFlash('success', 'Un lien a été modifié');

            return $this->redirectToRoute('music_cloud-all', ['mode' => 'tracks']);
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
        if ($this->isCsrfTokenValid('delete' . $cloudTrack->getId(), $request->query->get('_token'))) {
            $em->remove($cloudTrack);
            $em->flush();
            $this->addFlash('success', 'Une track a été supprimée');
            return $this->redirectToRoute('music_cloud-all', ['mode' => 'tracks']);
        }

        throw $this->createAccessDeniedException();
    }
}
