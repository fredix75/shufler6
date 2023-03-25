<?php

namespace App\Validator;

use App\Entity\Video;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class VideoValidator
{

    public static function validate(Video $video, ExecutionContextInterface $context): void
    {
        if (2 === $video->getCategorie() && !$video->getGenre()) {
            $context->buildViolation('Un genre doit être choisi')
                ->atPath('genre')
                ->addViolation()
            ;
        }

        if ($video->getAnnee()) {
            $periode = $video->getPeriode();
            $finPeriode = (int) substr($periode, - 4);
            $debutPeriode = (substr($periode, 0, 1) != '<') ? (int) substr($periode, 0, 4) : 0;
            if ((int) $video->getAnnee() < $debutPeriode || (int) $video->getAnnee() > $finPeriode) {
                $context->buildViolation('La Période ne correspond pas à l\'année')
                    ->atPath('periode')
                    ->addViolation()
                ;
            }
        }
    }

}