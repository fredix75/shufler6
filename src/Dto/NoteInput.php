<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class NoteInput
{
    public function __construct(
        #[Assert\Positive]
        public readonly ?int $id,

        #[Assert\Choice(choices: ['piece', 'radial'])]
        public readonly ?string $origin,

        #[Assert\Range(min: -1, max: 5)]
        public readonly ?int $note,
    ) {

    }
}
