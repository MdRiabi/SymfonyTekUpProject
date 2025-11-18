<?php

namespace App\Controller\Admin;

use App\Entity\AccountRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard_old')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $pendingRequestsCount = $entityManager->getRepository(AccountRequest::class)->count(['status' => AccountRequest::STATUS_PENDING]);

        return $this->render('admin/dashboard.html.twig', [
            'user' => $this->getUser(),
            'pending_requests_count' => $pendingRequestsCount,
        ]);
    }


}
