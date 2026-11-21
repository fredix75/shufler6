<?php

namespace App\Dto;

final class AlbumInput
{
    public function __construct(
        public readonly ?string $albumkey,
        public readonly ?string $albumpicture,
        public readonly ?string $youtubeKey,
        public readonly ?string $picture
    ) {}
}
