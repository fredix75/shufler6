<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class VideoQuery
{
    #[Assert\PositiveOrZero]
    public ?int $categorie = 0;

    public ?int $genre = 0;

    #[Assert\Regex(pattern: "/^(<1939|\d{4}-\d{4})$/i")]
    public string $periode = '';
    public string $search = '';
}
