<?php

namespace App\Controller\Admin;

use App\Entity\AccountRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class RequestManagementController extends AbstractController
{
    #[Route('/admin/requests', name: 'admin_manage_requests')]
    public function manageRequests(EntityManagerInterface $entityManager): Response
    {
        $pendingRequests = $entityManager->getRepository(AccountRequest::class)->findBy(['status' => AccountRequest::STATUS_PENDING]);

        return $this->render('admin/requests/manage.html.twig', [
            'pending_requests' => $pendingRequests,
        ]);
    }



    #[Route('/admin/requests/{id}/approve', name: 'admin_approve_request', methods: ['GET', 'POST'])]
    public function approveRequest(
        AccountRequest $accountRequest, 
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // 1. On crée une nouvelle entité Utilisateur
        $user = new Utilisateur();
        
        // 2. On pré-remplit avec les données de la demande
        $user->setEmail($accountRequest->getEmail());
        $user->setRole($accountRequest->getRole());

        // 3. On crée le formulaire et on le lie à notre utilisateur
        $form = $this->createForm(UserCreationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 4. Le formulaire est soumis, on traite la création
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashedPassword);
            
            // On sauvegarde le nouvel utilisateur
            $entityManager->persist($user);
            
            // On met à jour le statut de la demande
            $accountRequest->setStatus(AccountRequest::STATUS_APPROVED);
            $entityManager->persist($accountRequest);

            // On exécute les deux opérations
            $entityManager->flush();

            // TODO: Envoyer l'email de bienvenue ici en utilisant votre NotificationService
            // $this->notificationService->sendWelcomeEmail($user, $user->getPlainPassword());

            $this->addFlash('success', "L'utilisateur {$user->getEmail()} a été créé avec succès !");
            return $this->redirectToRoute('admin_manage_users');
        }

        // 5. Le formulaire n'est pas soumis, on affiche la page de création
        return $this->render('admin/users/createUser.html.twig', [
            'userCreationForm' => $form->createView(),
            'accountRequest' => $accountRequest, // On passe la demande pour le contexte
        ]);
    }

   

    #[Route('/admin/requests/{id}/reject', name: 'admin_reject_request', methods: ['POST'])]
    public function rejectRequest(AccountRequest $accountRequest, EntityManagerInterface $entityManager): Response
    {
        $accountRequest->setStatus(AccountRequest::STATUS_REJECTED);
        $entityManager->flush();

        $this->addFlash('success', 'Demande rejetée.');

        return $this->redirectToRoute('admin_manage_requests');
    }


    
}