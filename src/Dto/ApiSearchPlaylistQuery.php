<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ApiSearchPlaylistQuery
{
    public function __construct(
        public readonly ?string $search_api,
        public readonly ?string $artist,

        #[Assert\PositiveOrZero]
        public readonly ?int $id_album,

        #[Assert\PositiveOrZero]
        public ?int $id_cloudalbum
    ) {}
}
