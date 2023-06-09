<?php

namespace App\Controller;

use App\Entity\ChannelFlux;
use App\Form\ChannelFluxFormType;
use App\Repository\ChannelFluxRepository;
use App\Repository\FluxRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/channel', name: 'channel_')]
class ChannelFluxController extends AbstractController
{
    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    #[Security("is_granted('ROLE_AUTEUR')")]
    public function channelEdit(
        Request $request,
        ChannelFluxRepository $channelFluxRepository,
        ChannelFlux $channelFlux = null
    ): Response
    {
        if (!$channelFlux && '0' !== $request->get('id')) {
            $this->addFlash('danger', 'No Way !!');
            return $this->redirectToRoute('home');
        }

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
            $this->addFlash('success', 'Channel enregistré');

            return new Response(json_encode([
                'id' => $channelFlux->getId(),
                'name' => $channelFlux->getName(),
                'image' => $channelFlux->getImage(),
            ]), 200);
        }
        return $this->render('channel_flux/edit.html.twig', [
            'form' => $form,
            'channelflux' => $channelFlux
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    #[Security("is_granted('ROLE_AUTEUR')")]
    public function channelDelete(
        ChannelFluxRepository $channelFluxRepository,
        FluxRepository $fluxRepository,
        ChannelFlux $channelFlux
    ): Response
    {
        foreach ($channelFlux->getFlux() as $flux) {
            $flux->setChannel(null);
            $fluxRepository->save($flux, true);
        }
        $channelFluxRepository->remove($channelFlux, true);
        $this->addFlash('success', 'Channel bien supprimée');
        return $this->redirectToRoute('home');
    }

    #[Route('/delete_logo/{id}', name: 'delete_logo', requirements: ['id' => '\d+'])]
    #[Security("is_granted('ROLE_AUTEUR')")]
    public function channelDeleteLogo(
        ChannelFluxRepository $channelFluxRepository,
        ChannelFlux $channelFlux
    ): Response
    {
        $channelFlux->setImage(null);
        $channelFluxRepository->save($channelFlux, true);
        $this->addFlash('success', 'Logo supprimé');
//@todo faire en ajax
        return $this->redirectToRoute('channel_edit', ['id' => $channelFlux->getId()]);
    }
}
