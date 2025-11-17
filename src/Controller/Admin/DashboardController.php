<?php

namespace App\Controller\Admin;

use App\Repository\AccountRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_dashboard')]
class DashboardController extends AbstractController
{
    public function __invoke(AccountRequestRepository $accountRequestRepository): Response
    {
        // On compte les demandes ayant le statut 'PENDING'
        $pendingRequestsCount = $accountRequestRepository->count(['status' => AccountRequest::STATUS_PENDING]);

        return $this->render('admin/dashboard.html.twig', [
            'pending_requests_count' => $pendingRequestsCount,
        ]);
    }
}