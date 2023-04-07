<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vimeo\Vimeo;

#[Route('/other', name: 'other_')]
class OtherController extends AbstractController
{
    #[Route('/api_video', name: 'api_video')]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function searchApiVideoAction(Request $request, HttpClientInterface $httpClient): Response
    {
        $search = $idVideo = $wiki = null;
        $resultat = [];

        if ($request->get('search_api')) {
            $search = $request->get('search_api');

            if ($request->get('id_video')) {
                $idVideo = $request->get('id_video');
            }

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
            $response = $httpClient->request('GET', $this->getParameter('youtube_api_url').'/search', [
                'query' => [
                    'key'  => $this->getParameter('youtube_key'),
                    'q'   => $search,
                    'part' => 'snippet',
                    'maxResults' => 25,
                ],
                'headers' => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ]
            ]);

            $resultYouToube = json_decode($response->getContent(), true)['items'] ?? [];

            foreach ($resultYouToube as $item) {
                $resultat['youtube']['items'][] = [
                    'link' => $item['snippet']['thumbnails']['high']['url'] ?? null,
                    'name' => $item['snippet']['title'] ?? null,
                    'url'  => 'https://www.youtube.com/watch?v='.($item['id']['videoId'] ?? ''),
                    'author' => $item['snippet']['channelTitle'] ?? null,
                    'date' => date("d-m-Y", strtotime($item['snippet']['publishedAt'])),
                ];
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

        return $this->render('other/videosAPI.html.twig', [
            'search' => $search,
            'resultat' => $resultat,
            'idVideo' => $idVideo ?? 0,
            'wiki' => $wiki
        ]);
    }
}
