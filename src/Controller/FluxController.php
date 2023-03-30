<?php

namespace App\Controller;

use App\Entity\Flux;
use App\Form\FluxType;
use App\Repository\FluxRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/flux', name: 'flux_')]
class FluxController extends AbstractController
{
    #[Route('/podcasts', name: 'podcasts')]
    public function list(Request $request, FluxRepository $fluxRepository):Response
    {
        if ($request->isXmlHttpRequest()) {
            $url = $request->get('url');

            $contenu = '';
            try {
                if (@simplexml_load_file($url)->{'channel'}->{'item'}) {
                    $contenu = @simplexml_load_file($url)->{'channel'}->{'item'};
                }
            } catch (\Exception $e) {
                // nsp quoi faire :D
            }

            $page = $request->query->get('page');
            $debut = ($page - 1) * 6;

            for ($i = $debut; $i < $debut + 6; $i ++) {
                $infos[] = $contenu[$i];
            }

            return new Response(json_encode($infos));
        }

        $podcasts = $fluxRepository->getPodcasts();

        return $this->render('flux/podcasts.html.twig', [
            'podcasts' => $podcasts
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    #[Security("is_granted('ROLE_AUTEUR')")]
    public function edit(
        Request $request,
        FluxRepository $fluxRepository,
        Flux $flux = null
    ): Response
    {
        if (!$flux && '0' !== $request->get('id')) {
            $this->addFlash('danger', 'No Way !!');
            return $this->redirectToRoute('home');
        }
        $flux = $flux ?? new Flux();
        $form = $this->createForm(FluxType::class, $flux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $flux = $form->getData();
            if ($flux->getFile()) {
                $flux->setImage('new file');
            }

            $fluxRepository->save($flux, true);
            $this->addFlash('success', 'Flux enregistré');

            return $this->redirectToRoute('home');
        }

        return $this->render('flux/edit.html.twig', [
            'form'      => $form,
            'flux'      => $flux,
            'rss'       => $this->getParameter('shufler_flux')['rss'],
            'bizarre'   => [],
            'radios'    => $this->getParameter('shufler_flux')['radios'],
            'links'     => $this->getParameter('shufler_flux')['links'],
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    #[Security("is_granted('ROLE_AUTEUR')")]
    public function delete(
        FluxRepository $fluxRepository,
        Flux $flux
    ): Response
    {
        $fluxRepository->remove($flux, true);
        $this->addFlash('success', 'Flux bien supprimé');
        return $this->redirectToRoute('home');
    }

    #[Route('/delete_logo/{id}', name: 'delete_logo', requirements: ['id' => '\d+'])]
    #[Security("is_granted('ROLE_AUTEUR')")]
    public function deleteLogo(
        FluxRepository $fluxRepository,
        Flux $flux
    ): Response
    {
        $flux->setImage(null);
        $fluxRepository->save($flux, true);
        $this->addFlash('success', 'Logo supprimé');

        return $this->redirectToRoute('flux_edit', ['id' => $flux->getId()]);
    }
}
