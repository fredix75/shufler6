<?php

namespace App\Helper;

use App\Entity\MusicCollection\VTrack;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class MusicHelper
{

    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    public function handleParams(Request $request): array
    {
        $p = $request->query->all();

        return [
            'auteur' => $p['auteur'] ?? null,
            'album' => $p['album'] ?? null,
            'genres' => $p['genres'] ?? null,
            'annee' => $p['annee'] ?? null,
            'search' => $p['search'] ?? null,
            'note' => $p['note'] ?? null,
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
            'dataOrigin' => 'intro',
        ];

        $playlist = [$trackIntro['youtubeKey']];
        $list = [$trackIntro];

        $i = 0;
        foreach ($pieces as $piece) {
            if (!$piece instanceof VTrack) {
                continue;
            }
            $playlist[] = $piece->getYoutubeKey();
            $list[] = [
                'id' => $piece->getIdTrack(),
                'titre' => $piece->getTitle(),
                'auteur' => $piece->getAuthor(),
                'album' => $piece->getAlbumName(),
                'genre' => $piece->getKind(),
                'picture' => $piece->getAlbumPicture(),
                'annee' => $piece->getYear(),
                'note' => $piece->getNote(),
                'youtubeKey' => $piece->getYoutubeKey(),
                'dataOrigin' => $piece->getDataOrigin(),
            ];
            $i++;

            if ($i >= $musicParameters['max_random']) {
                break;
            }
        }

        return [$list, $playlist];
    }
}
