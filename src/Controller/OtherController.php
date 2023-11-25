<?php

namespace App\Controller;

use App\Helper\ApiRequester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Vimeo\Exceptions\VimeoRequestException;
use Vimeo\Vimeo;

#[Route('/other', name: 'other')]
class OtherController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws VimeoRequestException|ClientExceptionInterface
     */
    #[Route('/api_video', name: '_api_video')]
    #[IsGranted('ROLE_ADMIN')]
    public function searchApiVideo(Request $request, ApiRequester $apiRequester): Response
    {
        $search = $idVideo = $idTrack = $wiki = null;
        $resultat = [];

        if ($request->get('search_api')) {
            $search = $request->get('search_api');
            $idVideo = $request->get('id_video') ?? $idVideo;
            $idTrack = $request->get('id_track') ?? $idTrack;

            $resultat = [
                'youtube' => [
                    'label' => 'Youtube',
                    'items' => [],
                ],
                'vimeo' => [
                    'label' => 'Vimeo',
                    'items' => [],
                ],
            ];

            // Youtube
            $response = $apiRequester->sendRequest('youtube', '/search', [
                'q'   => $search,
                'maxResults' => 25,
            ]);

            if ($response->getStatusCode() === Response::HTTP_OK) {
                $resultYouToube = json_decode($response->getContent(), true)['items'] ?? [];

                foreach ($resultYouToube as $item) {
                    $resultat['youtube']['items'][] = [
                        'link' => $item['snippet']['thumbnails']['high']['url'] ?? null,
                        'name' => $item['snippet']['title'] ?? null,
                        'url' => 'https://www.youtube.com/watch?v=' . ($item['id']['videoId'] ?? ''),
                        'author' => $item['snippet']['channelTitle'] ?? null,
                        'date' => date("d-m-Y", strtotime($item['snippet']['publishedAt'])),
                    ];
                }
            }

            // Vimeo
            $lib = new Vimeo($this->getParameter('vimeo_id'), $this->getParameter('vimeo_secret'), $this->getParameter('vimeo_access_token'));

            $content = $lib->request('/videos', [
                'query' => $search,
                'per_page' => 25,
                'page' => 1
            ]);

            foreach ($content['body']['data'] as $item) {
                $resultat['vimeo']['items'][] = [
                    'link' => $item['pictures']['sizes'][1]['link'],
                    'name' => $item['name'],
                    'url'  => $item['link'],
                    'author' => $item['user']['name'],
                    'date' => date("d-m-Y", strtotime($item['created_time'])),
                ];
            }

            // Wikipedia
            /*
             * $urlWiki='https://fr.wikipedia.org/w/api.php?action=query&formatversion=2&generator=prefixsearch&gpssearch='.$search.'&gpslimit=10&prop=pageimages|pageterms&piprop=thumbnail&pithumbsize=50&pilimit=10&redirects=&wbptterms=description&format=json';
             *
             * $contentWiki=file_get_contents($urlWiki);
             *
             * $contentWiki=json_decode($contentWiki);
             * $wiki=array();
             * if($contentWiki){
             * for( $i=1;$i<=count($contentWiki->{'query'}->{'pages'});$i++){
             * if(isset($contentWiki->{'query'}->{'pages'}[$i]->{'thumbnail'})){
             * $wiki[]['image'] = $contentWiki->{'query'}->{'pages'}[$i]->{'thumbnail'}->{'source'};
             * }
             * if(isset($contentWiki->{'query'}->{'pages'}[$i]->{'title'})){
             * $wiki[]['title'] = $contentWiki->{'query'}->{'pages'}[$i]->{'title'};
             * }
             * }
             * }
             */
        }
        $params = [
            'search' => $search,
            'resultat' => $resultat,
            'idVideo' => $idVideo ?? 0,
            'wiki' => $wiki
        ];
        if ($idTrack) {
            $params['idTrack'] = $idTrack;
        }
        return $this->render('other/videosAPI.html.twig', $params);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/api_channel', name: '_api_channel')]
    #[IsGranted('ROLE_ADMIN')]
    public function searchApiChannel(Request $request, ApiRequester $apiRequester): Response
    {
        $search = $idChannel = null;
        $resultat = [];
        if ($request->get('search_api')) {
            $search = $request->get('search_api');

            $response = $apiRequester->sendRequest('youtube', '/search', [
                'q'   => $search,
                'type' => 'channel',
                'maxResults' => 5,
            ]);

            if ($response->getStatusCode() === Response::HTTP_OK) {
                $resultYouToube = json_decode($response->getContent(), true)['items'] ?? [];
                foreach ($resultYouToube as $item) {
                    $resultat[] = [
                        'link' => $item['snippet']['thumbnails']['high']['url'] ?? null,
                        'name' => $item['snippet']['title'] ?? null,
                        'url' => 'https://www.youtube.com/watch?v=' . ($item['id']['videoId'] ?? ''),
                        'author' => $item['snippet']['channelTitle'] ?? null,
                        'date' => date("d-m-Y", strtotime($item['snippet']['publishedAt'])),
                        'channelId' => $item['snippet']['channelId'],
                    ];
                }
            }
        }
        return $this->render('other/channelsAPI.html.twig', [
            'resultats' => $resultat,
            'search' => $search,
            'idChannel' => $idChannel
        ]);
    }

    #[Route('/api_playlist', name: '_api_playlist')]
    #[IsGranted('ROLE_ADMIN')]
    public function searchApiPlaylist(Request $request, ApiRequester $apiRequester): Response
    {
        $search = $idAlbum = null;
        $resultat = [];
        if ($request->get('search_api')) {
            $search = $request->get('search_api');
            $idAlbum = $request->get('id_album') ?? null;

            $response = $apiRequester->sendRequest('youtube', '/search', [
                'q'   => $search,
                'type' => 'playlist',
                'maxResults' => 50,
            ]);

            if ($response->getStatusCode() === Response::HTTP_OK) {
                $resultYouToube = json_decode($response->getContent(), true)['items'] ?? [];
                foreach ($resultYouToube as $item) {
                    $resultat[] = [
                        'link' => $item['snippet']['thumbnails']['high']['url'] ?? null,
                        'name' => $item['snippet']['title'] ?? null,
                        'url' => 'https://www.youtube.com/watch?v=' . ($item['id']['videoId'] ?? ''),
                        'author' => $item['snippet']['channelTitle'] ?? null,
                        'date' => date("d-m-Y", strtotime($item['snippet']['publishedAt'])),
                        'channelId' => $item['snippet']['channelId'],
                    ];
                }
            }
        }
        return $this->render('other/playlistsAPI.html.twig', [
            'resultats' => $resultat,
            'search' => $search,
            'idAlbum' => $idAlbum ?? 0,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/channel/handle', name: '_handle_channel')]
    public function handleChannel(Request $request, ApiRequester $apiRequester): Response
    {
        if ($request->isXmlHttpRequest()) {
            $channelId = $request->get('id');
            $response = $apiRequester->sendRequest('youtube', '/playlists', [
                'channelId'   => $channelId,
                'part' => 'snippet, contentDetails',
                'maxResults' => 75,
            ]);

            $playlists = json_decode($response->getContent(), true)['items'] ?? [];

            return $this->render('other/part/_playlists.html.twig', [
                'playlists' => $playlists,
            ]);
        }

        return new Response("Method not allowed", 405);
    }



    /**
     * @throws \Exception
     */
    #[Route('/launch', name: '_launch')]
    public function launchCommand(KernelInterface $kernel): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'shufler:update-music-track',
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();

        return new Response($content);
    }
}
