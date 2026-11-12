<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ApiSearchVideoQuery
{
    public function __construct(
        public readonly ?string $search_api,

        #[Assert\PositiveOrZero]
        public readonly ?int $id_video,

        #[Assert\PositiveOrZero]
        public readonly ?int $id_track,

        #[Assert\PositiveOrZero]
        public readonly ?int $id_cloud_track
    ) {}
}
