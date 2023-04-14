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
            'news' => $news
        ]);
    }

    #[Route('/playlists', name: 'playlists')]
    public function playlists(FluxRepository $fluxRepository): Response
    {
        $playlists = $fluxRepository->getPlaylists();

        return $this->render('/flux/playlists.html.twig', [
            'playlists' => $playlists
        ]);
    }

    #[Route('/handle', name: 'handle_file')]
    public function handleFlux(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->get('id');
            $url = $request->get('url');
            $type = $request->get('type');
            $contenu = '';
            try {
                if (@simplexml_load_file($url, null, LIBXML_NOCDATA)->{'channel'}->{'item'}) {
                    $contenu = @simplexml_load_file($url)->{'channel'}->{'item'};
                }
            } catch (\Exception $e) {
                return new Response('No data');
            }

            $page = $request->get('page');
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

            $template = sprintf('%s_%s_list.html.twig', 'flux/part/', $type);

            return $this->render($template, [
                'id' => $id,
                'datas' => $infos
            ]);
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

        if ($request->get('channelkey')) {
            $flux->setUrl('https://www.youtube.com/playlist?list='.$request->get('channelkey'));
            $flux->setImage($request->get('channelpicture'));
            $flux->setType(5);
        }

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
}
