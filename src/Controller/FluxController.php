<?php

namespace App\Controller;

use App\Entity\Flux;
use App\Form\FluxType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/flux', name: 'flux_')]
class FluxController extends AbstractController
{
    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '\id+'])]
    public function edit(Request $request, Flux $flux = null): Response
    {
        /**
        if (!$flux && '0' !== $request->get('id')) {
            $this->addFlash('danger', 'No Way !!');
   //         return $this->redirectToRoute('flux_list');
        }

        $flux = $flux ?? new Flux();

        $form = $this->createForm(FluxType::class, $flux);

        return $this->render('flux/edit.html.twig', [
            'form' => $form,
            'flux' => $flux
        ]);
         * */
        return new Response('ok');
    }
}
