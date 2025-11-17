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
    #[Route('/admin', name: 'app_admin')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $pendingRequestsCount = $entityManager->getRepository(AccountRequest::class)->count(['status' => AccountRequest::STATUS_PENDING]);

        return $this->render('admin/dashboard.html.twig', [
            'user' => $this->getUser(),
            'pending_requests_count' => $pendingRequestsCount,
        ]);
    }

    #[Route('/admin/users', name: 'admin_manage_users')]
    public function manageUsers(): Response
    {
        return $this->render('admin/users.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
