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

        $process = new Process(['python3', '../bin/test.py']);
        $process->run();
        $response = [];
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $response[] = $data;
            } else { // $process::ERR === $type
                $response[] = $data;
            }
        }
        return new Response(implode(', ', $response));

    }
}