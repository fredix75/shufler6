<?php

namespace App\Controller;

use App\Entity\ChannelFlux;
use App\Form\ChannelFluxFormType;
use App\Repository\ChannelFluxRepository;
use App\Repository\FluxRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/channel', name: 'channel')]
class ChannelFluxController extends AbstractController
{
    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'], defaults: ['id' => 0])]
    #[IsGranted('ROLE_AUTEUR')]
    public function channelEdit(
        Request               $request,
        ChannelFluxRepository $channelFluxRepository,
        ?ChannelFlux          $channelFlux
    ): Response
    {
        $channelFlux = $channelFlux ?? new ChannelFlux();
        $form = $this->createForm(ChannelFluxFormType::class, $channelFlux, [
            'action' => $this->generateUrl(
                $request->attributes->get('_route'),
                $request->attributes->get('_route_params')
            ),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $channelFlux = $form->getData();
            if ($channelFlux->getFile()) {
                // pour que ça persiste en cas de simple changement d'image
                $channelFlux->setImage('new file');
            }
            $channelFluxRepository->save($channelFlux, true);

            return new Response(json_encode([
                'id' => $channelFlux->getId(),
                'name' => $channelFlux->getName(),
                'image' => $channelFlux->getImage(),
            ]), Response::HTTP_OK);
        }

        return $this->render('channel_flux/edit.html.twig', [
            'form' => $form,
            'channelflux' => $channelFlux
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_AUTEUR')]
    public function channelDelete(
        Request $request,
        ChannelFluxRepository $channelFluxRepository,
        FluxRepository        $fluxRepository,
        ChannelFlux           $channelFlux
    ): Response
    {
        if ($this->isCsrfTokenValid('channel_delete'.$channelFlux->getId(), $request->get('_token'))) {
            foreach ($channelFlux->getFlux() as $flux) {
                $flux->setChannel(null);
                $fluxRepository->save($flux, true);
            }
            $channelFluxRepository->remove($channelFlux, true);
            $this->addFlash('success', 'Channel bien supprimée');
        }
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/delete_logo/{id}', name: '_delete_logo', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_AUTEUR')]
    public function channelDeleteLogo(
        Request $request,
        ChannelFluxRepository $channelFluxRepository,
        ChannelFlux           $channelFlux
    ): Response
    {
        $channelFlux->setImage(null);
        $channelFluxRepository->save($channelFlux, true);
        $this->addFlash('success', 'Logo supprimé');

        return $this->redirect($request->headers->get('referer'));
    }
}
