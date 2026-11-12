<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class DataTableQuery
{
    public function __construct(
        #[Assert\PositiveOrZero]
        public readonly ?int $length,

        #[Assert\PositiveOrZero]
        public readonly ?int $start,

        public readonly ?array $order,

        public readonly ?array $search
    ){}
}
