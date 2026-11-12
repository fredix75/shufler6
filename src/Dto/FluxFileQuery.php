<?php

namespace App\Dto;

class FluxFileQuery
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $url,
        public readonly ?string $type,
        public readonly ?int $page
    ) {}
}
