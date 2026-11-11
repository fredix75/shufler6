<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class DataTableQuery
{
    #[Assert\PositiveOrZero]
    public int $length = 0;

    #[Assert\PositiveOrZero]
    public int $start = 0;

    public array $order = [];

    public array $search = [];
}
