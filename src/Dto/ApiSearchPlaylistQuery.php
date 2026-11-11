<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ApiSearchPlaylistQuery
{
    public ?string $search_api = null;

    #[Assert\PositiveOrZero]
    public ?int $artist = null;

    #[Assert\PositiveOrZero]
    public ?int $id_album = null;

    #[Assert\PositiveOrZero]
    public ?int $id_cloudalbum = null;
}
