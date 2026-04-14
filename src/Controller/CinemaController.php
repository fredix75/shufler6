<?php

namespace App\Controller;

use App\Entity\Film;
use App\Entity\Genrefilm;
use App\Form\FilmType;
use App\Helper\ApiRequester;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cinema', name: 'cinema')]
#[IsGranted('ROLE_ADMIN')]
final class CinemaController extends AbstractController
{
    #[Route('/{id}', name: '_index', defaults: ['id' => 0])]
    public function index(Request $request, EntityManagerInterface $em, ApiRequester $apiRequester, ?Film $film = null): Response
    {
        if ($film && $request->query->get('delete') == true) {
            $em->remove($film);
            $em->flush();
            $this->addFlash('success', 'Film supprimé. Si on peut appeler ça un film...');
            return $this->redirectToRoute('cinema_index');
        }

        $suggests = [];

        if ($film && $request->query->get('getInfos')) {
            $response = $apiRequester->sendRequest('tmdb', '/search/movie', [
                'query' => $request->query->get('altName') ?? $film->getName(),
            ]);


            if ($response->getStatusCode() === Response::HTTP_OK) {
                $response = json_decode($response->getContent(), true) ?? [];
                if (!empty($response['results'])) {
                    $responses = $response['results'];
                    if (!empty($responses)) {
                        foreach ($responses as $index => $item) {
                            if (!empty($item['release_date']) && (new \DateTime($item['release_date']))->format('Y') <= 2002) {
                                $info['year'] = (new \DateTime($item['release_date']))->format('Y');
                            } elseif ((new \DateTime($item['release_date']))->format('Y') > 2002) {
                                continue;
                            }
                            $info['name'] = $item['title'];
                            $info['original_title'] = $item['original_title'];
                            $info['poster_path'] = $item['poster_path'];
                            $info['offset'] = $index;
                            $suggests[] = $info;
                        }
                    }
                } else {
                    $this->addFlash('warning', 'No suggests');
                }
            } else {
                $this->addFlash('danger', 'ERROR API on suggests');
            }
        }
        if ($film && $request->query->get('noRef') == 1) {
            $film->setYear(null)
                ->setOverview(null)
                ->setOriginalLanguage(null)
                ->setOriginalTitle(null)
                ->setTmdbId(null)
                ->setPosterPath(null)
                ->setBackdropPath(null)
                ->setPopularity(null)
                ->setGenres([]);
            $film->setVerified(true);
            $film->setNoref(true);
            $em->flush();
            $this->addFlash('success', 'Film no ref updated');
            return $this->redirectToRoute('cinema_index');
        }

        if ($film && $request->query->get('check') == 1) {
            $film->setVerified(true);
            $em->flush();
            $this->addFlash('success', 'Film updated');
            return $this->redirectToRoute('cinema_index');
        }

        if (!$film) {
            $film = $em->getRepository(Film::class)->findOneBy(['verified' => false]);
        }
        $genreLabels = $em->getRepository(Genrefilm::class)->findAll();
        $film->setGenresLabels($genreLabels);
        $film->setAltName($request->query->get('altName'));
        $form = $this->createForm(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $offset = $form->get('offset')->getData();
            $altName = $form->get('altName')->getData();
            $em->flush();
            return $this->redirectToRoute('cinema_get-film-ref', ['id' => $film->getId(), 'offset' => $offset - 1, 'altName' => $altName]);
        }

        return $this->render('cinema/index.html.twig', [
            'film' => $film,
            'form' => $form,
            'suggests' => $suggests,
        ]);
    }

    #[Route('/get-film-ref/{id}/{offset}', name: '_get-film-ref', requirements: ['id' => '\d+', 'offset' => '\d+'], defaults: ['offset' => 0])]
    public function getFilmRef(Request $request, ApiRequester $apiRequester, EntityManagerInterface $em, Film $movie, ?int $offset): Response
    {
        while (true) {
            $response = $apiRequester->sendRequest('tmdb', '/search/movie', [
                'query' => $request->query->get('altName') ?? $movie->getName(),
            ]);


            if ($response->getStatusCode() === Response::HTTP_OK) {
                $response = json_decode($response->getContent(), true) ?? [];
                if (!empty($response['results'])) {
                    $response = $response['results'][$offset];
                    if (!empty($response['release_date']) && (new \DateTime($response['release_date']))->format('Y') > 2002) {
                        $offset++;
                        continue;
                    }
                    $movie->setYear(!empty($response['release_date']) ? (new \DateTime($response['release_date']))->format('Y') : null);
                    $movie->setOverview($response['overview']);
                    $movie->setOriginalTitle($response['original_title']);
                    $movie->setOriginalLanguage($response['original_language']);
                    $movie->setTmdbId($response['id']);
                    $movie->setPosterPath($response['poster_path']);
                    $movie->setBackdropPath($response['backdrop_path']);
                    $movie->setPopularity($response['popularity']);
                    $movie->setGenres($response['genre_ids']);

                    $em->flush();
                    $this->addFlash('success', 'Movie updated !');
                } else {
                    $this->addFlash('warning', 'No result');
                }
            } else {
                $this->addFlash('danger', 'ERROR API');
            }
            break;
        }

        return $this->redirectToRoute('cinema_index', ['id' => $movie->getId(), 'altName' => $request->query->get('altName')]);
    }
}
