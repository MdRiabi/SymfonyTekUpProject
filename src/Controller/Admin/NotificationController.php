<?php

namespace App\Controller\Admin;

use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/admin/notifications/dropdown', name: 'admin_notifications_dropdown')]
    public function dropdown(NotificationRepository $notificationRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new Response('');
        }

        // Fetch fresh notifications from DB
        $notifications = $notificationRepository->findBy(
            ['utilisateur' => $user],
            ['dateCreation' => 'DESC'],
            5 // Limit to 5
        );

        return $this->render('admin/partials/_notifications_dropdown.html.twig', [
            'notifications' => $notifications
        ]);
    }

    #[Route('/admin/notifications/mark-all-read', name: 'admin_notifications_mark_all_read')]
    public function markAllRead(NotificationService $notificationService): Response
    {
        $user = $this->getUser();
        if ($user) {
            $notificationService->markAllAsRead($user);
            $this->addFlash('success', 'Toutes les notifications ont été marquées comme lues.');
        }
        
        return $this->redirectToRoute('admin_dashboard');
    }
}
