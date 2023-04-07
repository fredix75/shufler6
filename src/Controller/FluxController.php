<?php

namespace App\Controller;

use App\Entity\ChannelFlux;
use App\Entity\Flux;
use App\Form\ChannelFluxType;
use App\Form\FluxType;
use App\Repository\ChannelFluxRepository;
use App\Repository\FluxRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/flux', name: 'flux_')]
#[Security("is_granted('ROLE_USER')")]
class FluxController extends AbstractController
{
    #[Route('/podcasts', name: 'podcasts')]
    public function podcasts(FluxRepository $fluxRepository):Response
    {
        $podcasts = $fluxRepository->getPodcasts();

        return $this->render('flux/podcasts.html.twig', [
            'podcasts' => $podcasts
        ]);
    }

    #[Route('/news/{category}', name: 'news', requirements: ['category' => '\d+'])]
    public function news(FluxRepository $fluxRepository, int $category = 201): Response
    {
        $news = $fluxRepository->getNews($category);

        return $this->render('/flux/news.html.twig', [
            'news' =>$news
        ]);
    }

    #[Route('/handle', name: 'handle_file')]
    public function handleFlux(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $url = $request->get('url');

            $contenu = '';
            try {
                if (@simplexml_load_file($url, null, LIBXML_NOCDATA)->{'channel'}->{'item'}) {
                    $contenu = @simplexml_load_file($url)->{'channel'}->{'item'};
                }
            } catch (\Exception $e) {
                // nsp quoi faire :D
            }
;
            $page = $request->query->get('page');
            $debut = ($page - 1) * 6;
            $namespaces = $contenu ? $contenu->getNamespaces(true) : [];
            $infos = [];

            for ($i = $debut; $i < $debut + 6; $i ++) {
                if (empty($contenu[$i])) {
                    break;
                }
                $infos[$i] = $contenu[$i];
                $infos[$i]->title = stripcslashes($contenu[$i]->title);
                $infos[$i]->description = $contenu[$i]->description;
                if (!empty($namespaces['media']) && !empty($contenu[$i]->children($namespaces['media'])->attributes()->url)) {
                    $infos[$i]->media = $contenu[$i]->children($namespaces['media'])->attributes()->url;
                }
            }

            return new Response(json_encode($infos));
        }

        return new Response("Method not allowed", 405);
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
                // pour que ça persiste en cas de simple changement d'image
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

    #[Route('/channel_edit/{id}', name: 'channel_edit', requirements: ['id' => '\d+'])]
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
        $form = $this->createForm(ChannelFluxType::class, $channelFlux, [
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
        return $this->render('flux/edit_channel.html.twig', [
            'form' => $form,
            'channelflux' => $channelFlux
        ]);
    }

    #[Route('/channel_delete/{id}', name: 'channel_delete', requirements: ['id' => '\d+'])]
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

    #[Route('/channel_delete_logo/{id}', name: 'channel_delete_logo', requirements: ['id' => '\d+'])]
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
        return $this->redirectToRoute('flux_channel_edit', ['id' => $channelFlux->getId()]);
    }
}
