<?php

namespace App\Controller;

use App\Entity\Flux;
use App\Form\FluxFormType;
use App\Repository\FluxMoodRepository;
use App\Repository\FluxRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/flux', name: 'flux')]
#[IsGranted('ROLE_USER')]
class FluxController extends AbstractController
{
    #[Route('/podcasts', name: '_podcasts')]
    public function podcasts(FluxRepository $fluxRepository, Request $request, ParameterBagInterface $parameterBag):Response
    {
        if ($request->get('resource')) {
            $resources = scandir($parameterBag->get('uploads') . $parameterBag->get('resources')['downloads']);
            $resources = array_filter($resources, function($item) {
                return !is_dir($item);
            });

            return $this->render('flux/podcasts_resources.html.twig', [
                'resources' => $resources,
                'directory' => $parameterBag->get('resources')['downloads'],
            ]);
        }

        $podcasts = $fluxRepository->getPodcasts();

        return $this->render('flux/podcasts_flux.html.twig', [
            'podcasts' => $podcasts,
        ]);
    }

    #[Route('/news/{category}', name: '_news', requirements: ['category' => '\d+'])]
    public function news(FluxRepository $fluxRepository, FluxMoodRepository $fluxMoodRepository, int $category = null): Response
    {
        $defaultCategory = $category;
        $mood = $fluxMoodRepository->findOneBy(['name' => 'info', 'type' => 1]);
        if ($mood) {
            $defaultCategory = $mood->getId();
        }

        $news = $fluxRepository->getNews($category ?? $defaultCategory);

        return $this->render('/flux/news.html.twig', [
            'news' => $news,
            'categories' => $fluxMoodRepository->findBy(['type' => 1]),
            'default_categorie' => $defaultCategory
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
    public function links(FluxRepository $fluxRepository, FluxMoodRepository $fluxMoodRepository): Response
    {
        $liens = $fluxRepository->getLinks();

        return $this->render('flux/liens.html.twig', [
            'liens' => $liens,
            'categories' => $fluxMoodRepository->findBy(['type' => 4]),
        ]);
    }

    #[Route('/radios', name: '_radios')]
    #[IsGranted('ROLE_ADMIN')]
    public function radios(FluxRepository $fluxRepository, FluxMoodRepository $fluxMoodRepository): Response
    {
        $radios = $fluxRepository->getRadios();

        return $this->render('/flux/radios.html.twig', [
            'radios' => $radios,
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
        FluxMoodRepository $fluxMoodRepository,
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
            if ($flux->getFile()) {
                // pour que ça persiste en cas de simple changement d'image
                $flux->setImage('new file');
            }

            $fluxRepository->save($flux, true);
            $this->addFlash('success', 'Flux enregistré');
            $routeToRedirect = $this->getRouteToRedirect($flux->getType()->getName());

            return $this->redirectToRoute($routeToRedirect);
        }

        $fluxMoods = $fluxMoodRepository->findAll();
        $news = array_filter($fluxMoods, function($item) {
            return $item->getType()->getId() === 1;
        });
        array_walk_recursive($news, function($a) use (&$tabNews) {
            $tabNews[$a->getId()] = $a->getName();
        });
        $radios = array_filter($fluxMoods, function($item) {
            return $item->getType()->getId() === 3;
        });
        array_walk_recursive($radios, function($a) use (&$tabRadios) {
            $tabRadios[$a->getId()] = $a->getName();
        });
        $liens = array_filter($fluxMoods, function($item) {
            return $item->getType()->getId() === 4;
        });
        array_walk_recursive($liens, function($a) use (&$tabLiens) {
            $tabLiens[$a->getId()] = $a->getName();
        });

        return $this->render('flux/edit.html.twig', [
            'form'      => $form,
            'flux'      => $flux,
            'news'      => $tabNews,
            'radios'    => $tabRadios,
            'liens'     => $tabLiens,
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('FLUX_DELETE', "flux", "No pasaran")]
    public function delete(
        FluxRepository $fluxRepository,
        Flux $flux
    ): Response
    {
        $type = $flux->getType()->getName();
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

    private function getRouteToRedirect(string $type): string
    {
        $routeToRedirect = sprintf('flux_%s', $type);
        return $routeToRedirect !== 'flux_news' ? $routeToRedirect.'s' : $routeToRedirect;
    }
}
