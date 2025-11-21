<?php

namespace App\Controller\Admin;

use App\Entity\AccountRequest;
use App\Entity\Utilisateur;
use App\Form\Admin\ApproveUserCreationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
        
        // 2. On pré-remplit avec les données de la demande (email et rôle)
        // Ces champs ne sont PAS dans le formulaire
        $user->setEmail($accountRequest->getEmail());
        $user->setRole($accountRequest->getRole());

        // 3. On crée le formulaire sans les champs email et role
        $form = $this->createForm(ApproveUserCreationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 4. On s'assure que l'email et le rôle sont toujours définis
            // (au cas où ils auraient été écrasés)
            $user->setEmail($accountRequest->getEmail());
            $user->setRole($accountRequest->getRole());
            
            // 5. Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashedPassword);
            
            // 6. Sauvegarde de l'utilisateur
            $entityManager->persist($user);
            
            // 7. Mise à jour du statut de la demande
            $accountRequest->setStatus(AccountRequest::STATUS_APPROVED);
            $entityManager->persist($accountRequest);

            // 8. Exécution des opérations
            $entityManager->flush();

            // TODO: Envoyer l'email de bienvenue ici
            // $this->notificationService->sendWelcomeEmail($user, $user->getPlainPassword());

            $this->addFlash('success', "L'utilisateur {$user->getEmail()} a été créé avec succès !");
            return $this->redirectToRoute('admin_manage_users');
        }

        // 9. Affichage du formulaire
        return $this->render('admin/users/createProvedUser.html.twig', [
            'userCreationForm' => $form->createView(),
            'accountRequest' => $accountRequest,
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