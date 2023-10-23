<?php

namespace App\Controller;

use App\Entity\Flux;
use App\Form\FluxFormType;
use App\Repository\FluxRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/flux', name: 'flux')]
#[IsGranted('ROLE_USER')]
class FluxController extends AbstractController
{
    #[Route('/podcasts', name: '_podcasts')]
    public function podcasts(FluxRepository $fluxRepository):Response
    {
        $podcasts = $fluxRepository->getPodcasts();

        return $this->render('flux/podcasts.html.twig', [
            'podcasts' => $podcasts
        ]);
    }

    #[Route('/news/{category}', name: '_news', requirements: ['category' => '\d+'])]
    public function news(FluxRepository $fluxRepository, int $category = 201): Response
    {
        $news = $fluxRepository->getNews($category);

        return $this->render('/flux/news.html.twig', [
            'news' => $news,
            'categories' => $this->getParameter('shufler_flux')['news'],
        ]);
    }

    #[Route('/playlists', name: '_playlists')]
    #[IsGranted('ROLE_ADMIN')]
    public function playlists(FluxRepository $fluxRepository): Response
    {
        $playlists = $fluxRepository->getPlaylists();

        return $this->render('/flux/playlists.html.twig', [
            'playlists' => $playlists
        ]);
    }

    #[Route('/liens', name: '_liens')]
    #[IsGranted('ROLE_ADMIN')]
    public function links(FluxRepository $fluxRepository): Response
    {
        $liens = $fluxRepository->getLinks();

        return $this->render('flux/liens.html.twig', [
            'liens' => $liens,
            'categories' => $this->getParameter('shufler_flux')['liens'],
        ]);
    }

    #[Route('/radios', name: '_radios')]
    #[IsGranted('ROLE_ADMIN')]
    public function radios(FluxRepository $fluxRepository): Response
    {
        $radios = $fluxRepository->getRadios();

        return $this->render('/flux/radios.html.twig', [
            'radios' => $radios,
            'categories' => $this->getParameter('shufler_flux')['radios'],
        ]);
    }

    #[Route('/handle', name: '_handle_file')]
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
            } catch (Exception $e) {
                return new Response('No data - ' . $e->getMessage());
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

    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('FLUX_EDIT', "flux", "No pasaran")]
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
            $flux->setUrl(sprintf('https://www.youtube.com/playlist?list=%s', $request->get('channelkey')));
            $flux->setImage($request->get('channelpicture'));
            $flux->setType(5);
        }

        $form = $this->createForm(FluxFormType::class, $flux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $flux = $form->getData();
            if ($flux->getFile()) {
                // pour que ça persiste en cas de simple changement d'image
                $flux->setImage('new file');
            }

            $fluxRepository->save($flux, true);
            $this->addFlash('success', 'Flux enregistré');
            $routeToRedirect = $this->getRouteToRedirect($flux->getType());

            return $this->redirectToRoute($routeToRedirect);
        }

        return $this->render('flux/edit.html.twig', [
            'form'      => $form,
            'flux'      => $flux,
            'news'      => $this->getParameter('shufler_flux')['news'],
            'radios'    => $this->getParameter('shufler_flux')['radios'],
            'liens'     => $this->getParameter('shufler_flux')['liens'],
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('FLUX_DELETE', "flux", "No pasaran")]
    public function delete(
        FluxRepository $fluxRepository,
        Flux $flux
    ): Response
    {
        $type = $flux->getType();
        $fluxRepository->remove($flux, true);
        $this->addFlash('success', 'Flux bien supprimé');
        $routeToRedirect = $this->getRouteToRedirect($type);

        return $this->redirectToRoute($routeToRedirect);
    }

    #[Route('/delete_logo/{id}', name: '_delete_logo', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_AUTEUR')]
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

    private function getRouteToRedirect(int $type): string
    {
        $routeToRedirect = !empty($this->getParameter('shufler_flux')['types'][$type]) ? sprintf('flux_%s', $this->getParameter('shufler_flux')['types'][$type]) : 'home';

        return $routeToRedirect !== 'flux_news' ? $routeToRedirect.'s' : $routeToRedirect;
    }
}
