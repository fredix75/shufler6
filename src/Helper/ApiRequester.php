<?php

namespace App\Helper;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function PHPUnit\Framework\throwException;

class ApiRequester
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ParameterBagInterface $parameterBag
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function sendRequest(string $api, string $path = '', array $queryParams = [], string $method = 'GET'): ResponseInterface
    {
        switch ($api) {
            case 'youtube':
                $url = $this->parameterBag->get('youtube_api_url');
                $queryParams = [
                    'query' => array_merge([
                        'key' => $this->parameterBag->get('youtube_key'),
                        'part' => 'snippet',
                        'maxResults' => 1,
                    ], $queryParams),
                ];
                break;
            case 'vimeo':
                $url = $this->parameterBag->get('vimeo_api_url');
                break;
            case 'last_fm':
                $url = $this->parameterBag->get('music_collection')['last_fm_api_url'];
                $queryParams = [
                    'query' => array_merge([
                        'api_key' => $this->parameterBag->get('music_collection')['last_fm_key'],
                        'format' => 'json',
                    ], $queryParams)
                ];
                $queryParams = array_merge([

                ], $queryParams);
                break;
            default:
                throw new \Exception('no supported API');
        }
        $url = sprintf('%s%s', $url, $path);

        $params = array_merge([
            'headers' => [
                'Content-Type: application/json',
                'Accept: application/json',
            ]
        ], $queryParams);

        return $this->httpClient->request($method, $url, $params);
    }
}