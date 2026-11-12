<?php

namespace App\Dto;

class FilmQuery
{
    public function __construct(
        public readonly ?string $search,

        public readonly ?array $genres,

        public readonly ?string $director,

        public readonly ?string $year,
    ) {}
}
