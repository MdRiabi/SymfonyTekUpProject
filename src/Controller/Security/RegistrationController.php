<?php

namespace App\Controller\Security;

use App\Entity\AccountRequest;
use App\Form\RequestAccountFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/request-account', name: 'app_request_account')]
class RegistrationController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response {
        $form = $this->createForm(RequestAccountFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Créer une nouvelle demande de compte
            $accountRequest = new AccountRequest();
            $accountRequest->setEmail($data['email']);
            $accountRequest->setRole($data['role']);
            $accountRequest->setDescription($data['description']);
            $accountRequest->setStatus(AccountRequest::STATUS_PENDING);

            // Sauvegarder en base de données
            $entityManager->persist($accountRequest);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Votre demande de compte a été envoyée. Un administrateur la traitera bientôt.');

            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_signin');
        }

        return $this->render('security/RequestAccount.html.twig', [
            'requestAccountForm' => $form,
        ]);
    }
}