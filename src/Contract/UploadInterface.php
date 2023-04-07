<?php

namespace App\Contract;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadInterface
{
    public function getName(): ?string;

    public function getImage(): ?string;

    public function setImage(?string $image): self;

    public function getFile(): ?UploadedFile;

    public function setFile(?UploadedFile $file): self;

    public function getOldImage(): ?string;

    public function setOldImage(): self;
}