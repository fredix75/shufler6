<?php
namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;

class MusicHelper {

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

}
