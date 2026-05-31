<?php

namespace App\Helper;

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
}
