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

    #[Route('/admin/requests/{id}/approve', name: 'admin_approve_request', methods: ['POST'])]
    public function approveRequest(AccountRequest $accountRequest, EntityManagerInterface $entityManager): Response
    {
        $accountRequest->setStatus(AccountRequest::STATUS_APPROVED);
        $entityManager->flush();

        $this->addFlash('success', 'Demande approuvée.');

        return $this->redirectToRoute('admin_manage_requests');
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