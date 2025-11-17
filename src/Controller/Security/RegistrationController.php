<?php

namespace App\Controller\Security;

use App\Form\RequestAccountFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/request-account', name: 'app_request_account')]
class RegistrationController extends AbstractController
{
    public function __invoke(Request $request): Response {
        $form = $this->createForm(RequestAccountFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // TODO: Handle the account request (e.g., send email to admin, save to database)
            // For now, just add a flash message
            $this->addFlash('success', 'Votre demande de compte a été envoyée. Un administrateur la traitera bientôt.');

            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_signin');
        }

        return $this->render('security/RequestAccount.html.twig', [
            'requestAccountForm' => $form,
        ]);
    }
}