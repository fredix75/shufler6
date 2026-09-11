<?php
namespace App\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class FilmGenresTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (!$value) {
            return [];
        }

        return json_encode( $value);
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }

        return json_decode($value);
    }


}
