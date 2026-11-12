<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class VideoQuery
{
    public function __construct(
        #[Assert\PositiveOrZero]
        public readonly ?int $categorie,

        public readonly ?int $genre,

        #[Assert\Regex(pattern: "/^(<1939|\d{4}-\d{4})$/i")]
        public readonly ?string $periode,
        public readonly ?string $search,
    ){}
}
