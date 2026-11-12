<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class MusicQuery
{
    public function __construct(
        public readonly ?string $auteur,

        public readonly ?string $artist,

        public readonly ?string $album,

        public readonly ?array $genres,

        #[Assert\Positive]
        public readonly ?int $annee,

        public readonly ?string $search,

        #[Assert\PositiveOrZero]
        public readonly ?int $note,

        public readonly ?bool $random,

        public readonly ?bool $is_disambiguate,

        public readonly ?bool $isModal,
    ) {}
}
