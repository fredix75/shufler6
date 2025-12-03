<?php

namespace App\Controller;

use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\InputProcessor\SystemPromptInputProcessor;
use Symfony\AI\Agent\Memory\MemoryInputProcessor;
use Symfony\AI\Agent\Memory\StaticMemoryProvider;
use Symfony\AI\Platform\Bridge\OpenAi\PlatformFactory;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    public function promptAi(Request $request, HttpClientInterface $httpClient): Response
    {
        $form = $this->createFormBuilder()
            ->add('prompt', TextareaType::class)
            ->add('submit', SubmitType::class, ['label' => 'OK'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prompt = $form->getData()['prompt'];
            $platform = PlatformFactory::create($_ENV['OPENAI_API_KEY'], $httpClient);

            $systemPromptProcessor = new SystemPromptInputProcessor('Tu es un mentor qui a toujours des conseils sages et avisés. Tu parles français');

            $personalFacts = new StaticMemoryProvider(
                'Je suis un amateur de musique et d\'arts visuels vintage.',
            );

            $memoryProcessor = new MemoryInputProcessor($personalFacts);

            $agent = new Agent(
                $platform,
                'gpt-4o-mini',
                [$systemPromptProcessor, $memoryProcessor]
            );

            $messages = new MessageBag(Message::ofUser($prompt));
            $result = $agent->call($messages);

            return $this->render('test/ai.html.twig', [
                'form' => $form,
                'reponse' => $result->getContent().\PHP_EOL,
            ]);
        }

        return $this->render('test/ai.html.twig', [
            'form' => $form,
        ]);
    }
}
