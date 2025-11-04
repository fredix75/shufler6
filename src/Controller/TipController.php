<?php

namespace App\Controller;

use App\Entity\Tip;
use App\Form\TipFormType;
use App\Repository\TipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tip', name: 'tip')]
#[IsGranted('ROLE_ADMIN')]
class TipController extends AbstractController
{
    #[Route('/list/{id}', name: '_list', requirements: ['id' => '\d+'], defaults: ['id' => null])]
    public function index(?Tip $tip, TipRepository $tipRepository, Request $request, EntityManagerInterface $em): Response
    {
        $tip = $tip ?? new Tip();
        $tipForm = $this->createForm(TipFormType::class, $tip);
        $tipForm->handleRequest($request);
        if ($tipForm->isSubmitted() && $tipForm->isValid()) {
            $tip = $tipForm->getData();
            $em->persist($tip);
            $em->flush();
            $this->addFlash('success', 'Une note a été ajoutée');
            return $this->redirectToRoute($request->attributes->get('_route'));
        }

        $tips = $tipRepository->findBy([], ['dateInsert' => 'DESC'], 12);

        return $this->render('tip/tips.html.twig', [
            'form' => $tipForm,
            'tips' => $tips,
            'tip'  => $tip,
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(Tip $tip, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'. $tip->getId(), $request->get('_token'))) {
            $em->remove($tip);
            $em->flush();
            $this->addFlash('success', 'Une note a été supprimée');
            return $this->redirectToRoute('tip_list');
        } else {
            throw $this->createAccessDeniedException('Impossible de supprimer le note');
        }

    }
}
