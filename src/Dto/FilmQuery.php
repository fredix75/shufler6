<?php

namespace App\Dto;

class FilmQuery
{
    public ?string $search = null;

    public array $genres = [];

    public ?string $director = null;

    public ?string $year = null;
}
