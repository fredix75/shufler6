<?php

namespace App\Helper;

use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: "weather_tool", description: "Retourne la météo pour une ville donnée.")]
class AiTools
{

    public function __invoke(string $city): string
    {
        return "Il fait 23°C à $city";
    }
}
