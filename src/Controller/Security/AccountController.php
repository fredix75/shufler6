<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\AccountFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'account')]
    public function index(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response
    {
        $isCreation = false;
        if (!empty($this->getUser())) {
            $user = $this->getUser();
        } else {
            $user = new User();
            $isCreation = true;
        }

        $form = $this->createForm(AccountFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('password')->get('plainPassword')->getData()
            );

            $user->setEmail($form->get('email')->getData());
            $user->setPassword($encodedPassword);
            if (empty($this->getUser())) {
                $user->setRoles(['ROLE_USER']);
            }

            $userRepository->save($user, true);

            if ($isCreation) {
                $this->addFlash('success', 'Profil crée');
            } else {
                $this->addFlash('success', 'Profil modifié');
            }

            return $this->redirectToRoute('home');
        }

        return $this->render('security/account.html.twig', [
            'form' => $form,
        ]);
    }
}
