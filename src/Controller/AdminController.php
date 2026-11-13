<?php

namespace App\Controller;

use App\Form\ExtraNotationType;
use App\Helper\ApiRequester;
use App\Helper\VideoHelper;
use App\Repository\MusicCollection\TrackRepository;
use App\Repository\RadialRepository;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/admin', name: 'admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/dead_videos', name: '_dead_videos')]
    public function deadVideos(VideoRepository $videoRepository, ApiRequester $apiRequester, VideoHelper $videoHelper): Response
    {
        $videos = $videoRepository->findBy(['published' => true]);
        $deadVideos = [];

        foreach ($videos as $video) {
            if ($videoHelper->getPlatform($video->getLien()) !== VideoHelper::YOUTUBE) {
                continue;
            }

            $key = $videoHelper->getIdentifer($video->getLien(), VideoHelper::YOUTUBE);
            $response = $apiRequester->sendRequest(VideoHelper::YOUTUBE, '/videos', [
                'part' => 'id',
                'id' => $key, 1
            ]);

            if ($response->getStatusCode() === Response::HTTP_OK) {
                $response = json_decode($response->getContent(), true);
                if (empty($response['items'])) {
                    $deadVideos[] = $video;
                }
            }
        }

        return $this->render('admin/dead_videos.html.twig', [
            'videos' => $deadVideos,
        ]);
    }

    #[Route('/extra-note', name: '_extra_note')]
    public function listExtraNote(Request $request, TrackRepository $trackRepository): Response
    {
        $tracks = $trackRepository->createQueryBuilder('t')
            ->where('t.extraNote > 0 AND t.note IS NULL OR t.extraNote < 0 AND t.note > 0')
            ->getQuery()
            ->getResult();
        $tracks4 = array_merge(array_filter($tracks, fn($t) => $t->getExtraNote() > 0));
        $tracks0 = array_merge(array_filter($tracks, fn($t) => $t->getExtraNote() == -1 && $t->getNote() > 0));

        $form = $this->createForm(ExtraNotationType::class, null, [
            'tracks' => $tracks4,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $tracks = $form->get('extra_graduated_tracks')->getData();
            foreach ($tracks as $track) {
                $track->setNote(4);
                $track->setExtraNote(null);
            }
            $trackRepository->save($track, true);

            $this->addFlash('success', 'La note de ces titres a été mise à jour');
            return $this->redirectToRoute('admin_extra_note');
        }

        return $this->render('admin/extra_notes.html.twig', [
            'form' => $form,
            'tracks_4' => $tracks4,
            'tracks_0' => $tracks0,
        ]);
    }

    #[Route('/stats/tracks', name: '_stats_tracks')]
    public function musicCollStats(TrackRepository $trackRepository, ChartBuilderInterface $chartBuilder): Response
    {
        $genresStats = $trackRepository->createQueryBuilder('t')
            ->select('t.genre, COUNT(DISTINCT t.id) as nb')
            ->groupBy('t.genre')
            ->getQuery()->getResult();

        $yearsStats = $trackRepository->createQueryBuilder('t')
            ->select('t.annee, count(t.id) as nb')
            ->addSelect('SUM(CASE WHEN t.youtubeKey <> :nope THEN 1 ELSE 0 END) AS tracked')
            ->setParameter('nope', 'nope')
            ->groupBy('t.annee')
            ->orderBy('t.annee', \SortDirection::Descending)
            ->having('t.annee <> :null AND t.annee > :seuil')
            ->setParameter('null', '')
            ->setParameter('seuil', 1920)
            ->getQuery()->getResult();

        $genresChart = $chartBuilder->createChart(Chart::TYPE_PIE);
        $genresChart->setData([
            'labels' => array_map(fn($stat) => $stat['genre'], $genresStats),
            'datasets' => [
                [
                    'data' => array_map(fn($stat) => (int)$stat['nb'], $genresStats),
                ]
            ]
        ]);

        $genresChart->setOptions([
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
        ]);

        $yearChart = $chartBuilder->createChart(Chart::TYPE_BAR);

        $yearChart->setData([
            'labels' => array_map(fn($stat) => $stat['annee'], $yearsStats),
            'backgroundColor' => 'rgb(45,45,45)',
            'datasets' => [
                [
                    'label' => 'Non trackés',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'data' => array_map(fn($stat) => (int)$stat['nb'] - (int)$stat['tracked'], $yearsStats),
                ],
                [
                    'label' => 'Trackés',
                    'backgroundColor' => 'rgb(54, 162, 235)',
                    'data' => array_map(fn($stat) => (int)$stat['tracked'], $yearsStats),
                ],
            ],
        ]);

        $yearChart->setOptions([
            'animation' => true,
            'responsive' => true,
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                    'suggestedMax' => 1600,
                ],
            ],
        ]);

        return $this->render('admin/music_stats.html.twig', [
            'chart_genres' => $genresChart,
            'chart_years' => $yearChart,
        ]);

    }

    #[Route('/radial', name: '_radial')]
    public function radialStats(ChartBuilderInterface $chartBuilder, RadialRepository $radialRepository): Response
    {
        $stats = $radialRepository->createQueryBuilder('r')
            ->select('r.year, count(r.id) as nb')
            ->addSelect('SUM(CASE WHEN r.youtubeKey IS NOT NULL THEN 1 ELSE 0 END) AS tracked')
            ->groupBy('r.year')
            ->orderBy('r.year', \SortDirection::Descending)
            ->getQuery()->getResult();

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => array_map(fn($stat) => $stat['year'], $stats),
            'backgroundColor' => 'rgb(45,45,45)',
            'datasets' => [
                [
                    'label' => 'Non trackés',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'data' => array_map(fn($stat) => (int)$stat['nb'] - (int)$stat['tracked'], $stats),
                ],
                [
                    'label' => 'Trackés',
                    'backgroundColor' => 'rgb(54, 162, 235)',
                    'data' => array_map(fn($stat) => (int)$stat['tracked'], $stats),
                ],
            ],
        ]);

        $chart->setOptions([
            'animation' => true,
            'responsive' => true,
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                    'suggestedMax' => 10000,
                ],
            ],
        ]);

        return $this->render('admin/radial_stats.html.twig', [
            'chart' => $chart,
        ]);
    }

}
