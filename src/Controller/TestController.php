<?php

namespace App\Controller;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Bridge\OpenAi\Factory;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/test', name: 'test')]
class TestController extends AbstractController
{

    #[Route('/color', name: '_color')]
    public function test(Request $request): Response
    {

        $link = $request->query->get('link');
        $process = new Process(['python3',
            '../bin/dominant_color_finder.py',
           $link,
            // 'https://www.thoughtco.com/thmb/OVVzRivlUr6QFRi9fVabr0blZ-k=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/chimpanzee---pan-troglodytes-troglodytes--831042278-5a5e4c81b39d03003785777f.jpg'
        ]);
        $process->run();
        $output = '';
        if ($process->isSuccessful()) {
            // Récupérez la sortie du script Python
            $output = $process->getOutput();
        } else {
            // Si une erreur se produit, affichez l'erreur
            $output = 'Erreur : ' . $process->getErrorOutput();
        }
        return new Response($output);

    }

    #[Route('/ai', name: '_ai')]
    public function promptAi(Request $request, AgentInterface $agent): Response
    {
        $form = $this->createFormBuilder()
            ->add('prompt', TextareaType::class)
            ->add('submit', SubmitType::class, ['label' => 'OK'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prompt = $form->getData()['prompt'];

            $platform = Factory::createPlatform($this->getParameter('ai')['openai_api_key']);
            //$vectorResult = $platform->invoke('text-embedding-3-small', 'What is the capital of France?');

            // Generate a text completion with GPT, returns a Symfony\AI\Platform\Result\TextResult
            $response = $platform->invoke('gpt-4o-mini', new MessageBag(Message::ofUser('What is the capital of France?')));
            dd($response->asText());
            return $this->render('test/ai.html.twig', [
                'form' => $form,
                'reponse' => $response,
            ]);

        }

        return $this->render('test/ai.html.twig', [
            'form' => $form,
        ]);
    }
}
