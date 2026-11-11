<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ApiSearchVideoQuery
{
    public ?string $search_api = null;

    #[Assert\PositiveOrZero]
    public ?int $id_video = null;

    #[Assert\PositiveOrZero]
    public ?int $id_track = null;

    #[Assert\PositiveOrZero]
    public ?int $id_cloud_track = null;
}
