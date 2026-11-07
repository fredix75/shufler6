<?php

namespace App\Helper;

use App\Entity\MusicCollection\Piece;
use App\Entity\MusicCollection\Track;
use App\Entity\MusicCollection\VTrack;
use App\Entity\Radial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class MusicHelper
{

    public function __construct(private readonly EntityManagerInterface $em, private readonly ParameterBagInterface $parameterBag)
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
            if (\in_array($piece->getYoutubeKey(), $playlist, true)) {
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

    public function updateKeyForAll(string $key, string $author, string $title): void
    {
        $this->em
            ->createQueryBuilder()
            ->update(Piece::class, 'p')
            ->set('p.youtubeKey', ':key')
            ->setParameter('key', $key)
            ->where('p.auteur = :auteur')
            ->andWhere('p.titre = :titre')
            ->setParameter('auteur', $author)
            ->setParameter('titre', $title)
            ->getQuery()->execute();

        $this->em
            ->createQueryBuilder()
            ->update(Radial::class, 'r')
            ->set('r.youtubeKey', ':key')
            ->setParameter('key', $key)
            ->where('r.author = :auteur')
            ->andWhere('r.name = :titre')
            ->setParameter('auteur', $author)
            ->setParameter('titre', $title)
            ->getQuery()->execute();
    }

    public function updateNoteForAll(string $key, ?int $note, string $type): void
    {
        if ($type === Track::class) {
            $this->em
                ->createQueryBuilder()
                ->update(Track::class, 't')
                ->set('t.note', ':note')
                ->setParameter('note', $note ?? -1)
                ->where('t.youtubeKey = :key')
                ->setParameter('key', $key)
                ->getQuery()->execute();
        } else {
            $this->em
                ->createQueryBuilder()
                ->update(Piece::class, 'p')
                ->set('p.extraNote', ':note')
                ->setParameter('note', $note ?? -1)
                ->where('p.youtubeKey = :key')
                ->setParameter('key', $key)
                ->getQuery()->execute();
        }

        $this->em
            ->createQueryBuilder()
            ->update(Radial::class, 'r')
            ->set('r.note', ':note')
            ->setParameter('note', $note === -1 ? null : $note)
            ->where('r.youtubeKey = :key')
            ->setParameter('key', $key)
            ->getQuery()->execute();
    }

    public function updateExtraNoteForAll(string $key, ?int $note): void
    {
        $this->em
            ->createQueryBuilder()
            ->update(Piece::class, 'p')
            ->set('p.extraNote', ':note')
            ->setParameter('note', $note ?? -1)
            ->where('p.youtubeKey = :key')
            ->setParameter('key', $key)
            ->getQuery()->execute();

        $this->em
            ->createQueryBuilder()
            ->update(Radial::class, 'r')
            ->set('r.note', ':note')
            ->setParameter('note', $note === -1 ? null : $note)
            ->where('r.youtubeKey = :key')
            ->setParameter('key', $key)
            ->getQuery()->execute();
    }
}
