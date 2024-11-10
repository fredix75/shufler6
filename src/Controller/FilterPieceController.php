<?php

namespace App\Controller;

use App\Entity\FilterPiece;
use App\Form\FilterPieceFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/music', name: 'music')]
#[IsGranted('ROLE_ADMIN')]
class FilterPieceController extends AbstractController
{
    #[Route('/filter-piece/edit/{id}', name: '_filter_piece_edit', requirements: ['id' => '\d+'], defaults: ['id' => 0])]
    public function edit(?FilterPiece $filterPiece, Request $request, EntityManagerInterface $em): Response
    {
        $filterPiece = $filterPiece ?? new FilterPiece();

        $form = $this->createForm(FilterPieceFormType::class, $filterPiece);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($filterPiece);
            $em->flush();

            $this->addFlash('success', 'Une liste a été ajoutée');
            return $this->redirectToRoute('music_filter_couch');
        }

        return $this->render('filter_piece/edit.html.twig', [
            'form' => $form,
            'filter_piece' => $filterPiece,
        ]);
    }
}
