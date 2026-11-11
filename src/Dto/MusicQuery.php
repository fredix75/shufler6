<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class MusicQuery
{
    public ?string $auteur = null;

    public ?string $artist = null;

    public ?string $album = null;

    public ?array $genres = [];

    #[Assert\Positive]
    public ?int $annee = null;

    public ?string $search = null;

    #[Assert\PositiveOrZero]
    public ?int $note = null;

    public ?bool $random = false;

    public bool $is_disambiguate = false;

    public bool $isModal = false;
}
