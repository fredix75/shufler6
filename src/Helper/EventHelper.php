<?php

namespace App\Helper;

use Symfony\Component\String\UnicodeString;

class EventHelper
{
    public function getDateIntervall(int $index): array
    {
        $firstDate = new \DateTime('1901-01-05');
        $date = clone $firstDate;
        $date->modify("$index weeks");
        $startDate = clone $date;
        $startDate->modify("-1 weeks");

        return [$startDate, $date];
    }

    public function sanitizeEvents(array $events): array
    {
        //dd($events);
        return array_values(array_reduce($events, function ($carry, $item) {
            $d = new UnicodeString(mb_strtolower($item->getDescription()));
            $carry[$d->ascii()->toString()] ??= $item;
            return $carry;
        }, []));
    }
}
