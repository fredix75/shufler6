<?php

namespace App\Controller;

use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Agent\Exception\ExceptionInterface;
use Symfony\AI\Agent\InputProcessor\SystemPromptInputProcessor;
use Symfony\AI\Agent\Memory\MemoryInputProcessor;
use Symfony\AI\Agent\Memory\StaticMemoryProvider;
use Symfony\AI\AiBundle\Exception\RuntimeException;
use Symfony\AI\Platform\Bridge\OpenAi\PlatformFactory;
use Symfony\AI\Platform\Exception\RateLimitExceededException;
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
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function PHPUnit\Framework\isInstanceOf;
use function PHPUnit\Framework\throwException;

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

            $messages = new MessageBag(Message::ofUser($prompt));

            try {
                $result = $agent->call($messages);
                $response = $result->getContent().\PHP_EOL;
                //$response = 'ok';
            } catch (\Throwable $e) {
                $response = "Sorry : Rate Limit Exception ! {$e->getMessage()}";
            } finally {
                try {
                    return new Response('ok');
                } catch(\Throwable $e) {
                    dd($e);
                }
            }

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
