<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{

    #[Route('/test', name: 'test')]
    public function test(): Response
    {

        $process = new Process(['python3',
            '../bin/dominant_color_finder.py',
           'https://st4.depositphotos.com/10484988/37877/i/450/depositphotos_378770748-stock-photo-starry-sky-blue-sky-together.jpg',
            // 'https://www.thoughtco.com/thmb/OVVzRivlUr6QFRi9fVabr0blZ-k=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/chimpanzee---pan-troglodytes-troglodytes--831042278-5a5e4c81b39d03003785777f.jpg'
        ]);
        $process->run();
        $output = '';
        if ($process->isSuccessful()) {
            // Récupérez la sortie du script Python
            $output = $process->getOutput();
        } else {
            // Si une erreur se produit, affichez l'erreur
            echo 'Erreur : ' . $process->getErrorOutput();
        }
        return new Response($output);

    }
}