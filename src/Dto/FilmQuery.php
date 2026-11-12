<?php

namespace App\Dto;

class FilmQuery
{
    public function __construct(
        public ?string $search,

        public ?array $genres,

        public ?string $director,

        public ?string $year,
    ) {}
}
