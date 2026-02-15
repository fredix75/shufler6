<?php
namespace App\Helper;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class MusicHelper {

    public function __construct(private readonly ParameterBagInterface $parameterBag) {}

    public function handleParams(Request $request): array
    {
        $p = $request->query->all();

        return [
            'auteur' => $p['auteur'] ?? null,
            'album' => $p['album'] ?? null,
            'genres' => $p['genres'] ?? null,
            'annee' => $p['annee'] ?? null,
            'search' => $p['search'] ?? null,
            'hasYoutubeKey' => true,
            'is_disambiguate' => $p['is_disambiguate'] ?? false,
        ];
    }

    public function buildPlaylist(array $pieces): array
    {
        $musicParameters = $this->parameterBag->get('music_collection');
        $videoParameters = $this->parameterBag->get('shufler_video');

        $trackIntro = [
            'titre' => ' * * * * * L O A D I N G * * * * * ',
            'auteur' => '',
            'album' => '',
            'annee' => null,
            'youtubeKey' => $videoParameters['intro_couch'],
        ];

        $playlist = [$trackIntro['youtubeKey']];
        $list = [$trackIntro];

        $i = 0;
        foreach ($pieces as $piece) {
            if (!\in_array($piece['youtubeKey'], $playlist) && $piece['youtubeKey'] !== 'nope') {
                $playlist[] = $piece['youtubeKey'];
                $list[] = $piece;
                $i++;
            }
            if ($i >= $musicParameters['max_random']) {
                break;
            }
        }

        return [$list, $playlist];
    }
}
