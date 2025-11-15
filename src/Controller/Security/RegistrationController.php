<?php

namespace App\Controller\Security;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/signup', name: 'app_signup')]
class RegistrationController extends AbstractController
{
    public function __invoke(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash du mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $user->getPassword())
            );

            // Définir le rôle par défaut (ex: ROLE_MEMBER)
            // Si vous avez un système de rôles, ajustez ici
            // $user->setRoles(['ROLE_MEMBER']);

            $entityManager->persist($user);
            $entityManager->flush();

            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_signin');
        }

        return $this->render('security/signup.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}