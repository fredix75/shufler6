<?php

namespace App\EntityListener;

use App\Entity\MusicCollection\Piece;
use App\Entity\Radial;
use App\Helper\MusicHelper;
use App\Helper\VideoHelper;
use App\Repository\MusicCollection\PieceRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

#[AsEntityListener(event: Events::prePersist, entity: Piece::class)]
#[AsEntityListener(event: Events::prePersist, entity: Radial::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Piece::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Radial::class)]
class PieceListener
{
    public function __construct(private readonly VideoHelper $videoHelper, private readonly MusicHelper $musicHelper, private readonly PropertyAccessorInterface $propertyAccessor)
    {
    }

    public function prePersist(Piece|Radial $piece): void
    {
        $key = $piece->getYoutubeKey();
        if ($key && ($platform = $this->videoHelper->getPlatform($key)) === VideoHelper::YOUTUBE) {
            $key = $this->videoHelper->getIdentifer($key, $platform);
            $piece->setYoutubeKey($key);
        }
        if ($key) {
            $this->musicHelper->updateKeyForAll($key, $piece->getAuteur(), $piece->getTitre());
            if ($piece->getNote()){
                $this->musicHelper->updateNoteForAll($key, $piece->getNote(), get_class($piece));
            }
            if ($this->propertyAccessor->isReadable($piece, 'extraNote') && $piece->getExtraNote()){
                $this->musicHelper->updateExtraNoteForAll($key, $piece->getExtraNote());
            }
        }

    }

    public function preUpdate(Piece|Radial $piece, PreUpdateEventArgs $args): void
    {
        $key = $piece->getYoutubeKey();
        if ($key && ($platform = $this->videoHelper->getPlatform($key)) === VideoHelper::YOUTUBE) {
            $key = $this->videoHelper->getIdentifer($key, $platform);
            $piece->setYoutubeKey($key);
        }
        if ($key) {
            if ($args->hasChangedField('youtubeKey')) {
                $this->musicHelper->updateKeyForAll($key, $piece->getAuteur(), $piece->getTitre());
            }
            if ($args->hasChangedField('note')){
                $this->musicHelper->updateNoteForAll($key, $piece->getNote(), get_class($piece));
            }
            if ($args->hasChangedField('extraNote') && $this->propertyAccessor->isReadable($piece, 'extraNote') && $piece->getExtraNote()){
                $this->musicHelper->updateExtraNoteForAll($key, $piece->getExtraNote());
            }
        }
    }

}
