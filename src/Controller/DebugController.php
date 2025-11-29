<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\Projet;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DebugController extends AbstractController
{
    #[Route('/debug/notifications', name: 'debug_notifications')]
    public function index(
        UtilisateurRepository $userRepo,
        NotificationService $notificationService,
        EntityManagerInterface $em
    ): Response {
        $output = "<h1>Debug Notifications</h1>";

        // 1. Test Admin Retrieval
        $admins = $userRepo->findByRole('ROLE_ADMIN');
        $output .= "<h2>1. Admins Found: " . count($admins) . "</h2>";
        
        // DEBUG: List all roles
        $roles = $em->getRepository(\App\Entity\Role::class)->findAll();
        $output .= "<h3>Available Roles in DB:</h3><ul>";
        foreach ($roles as $role) {
            $output .= "<li>ID: " . $role->getId() . " - Name: '" . $role->getNomRole() . "'</li>";
        }
        $output .= "</ul>";

        // DEBUG: Current User Role
        $user = $this->getUser();
        if ($user) {
            $output .= "<h3>Current User Role:</h3>";
            $role = $user->getRole();
            if ($role) {
                $output .= "ID: " . $role->getId() . " - Name: '" . $role->getNomRole() . "'";
            } else {
                $output .= "No role assigned.";
            }
        }

        // 2. Test Notification Creation
        $output .= "<h2>2. Testing Notification Creation</h2>";
        try {
            if (count($admins) > 0) {
                $notificationService->notifyAdmins(
                    'Debug Test',
                    'Ceci est un test de debug ' . date('H:i:s'),
                    'info',
                    null
                );
                $output .= "✅ Notification service called without error.<br>";
            } else {
                $output .= "⚠️ No admins found, cannot test notification sending.<br>";
            }
        } catch (\Exception $e) {
            $output .= "❌ Error sending notification: " . $e->getMessage() . "<br>";
        }

        // 3. Check Database for Notifications
        $output .= "<h2>3. Last 5 Notifications in DB</h2>";
        $notifications = $em->getRepository(Notification::class)->findBy([], ['dateCreation' => 'DESC'], 5);
        foreach ($notifications as $notif) {
            $output .= "ID: " . $notif->getId() . 
                       " - User: " . ($notif->getUtilisateur() ? $notif->getUtilisateur()->getEmail() : 'null') . 
                       " - Title: " . $notif->getTitre() . 
                       " - Date: " . $notif->getDateCreation()->format('Y-m-d H:i:s') . "<br>";
        }

        // 4. Check Current User Notifications
        $user = $this->getUser();
        $output .= "<h2>4. Current User Status</h2>";
        if ($user) {
            $output .= "User: " . $user->getEmail() . "<br>";
            $userNotifs = $user->getNotifications();
            $output .= "Notifications (via relation): " . count($userNotifs) . "<br>";
        } else {
            $output .= "Not logged in.<br>";
        }

        // 5. Backfill Option
        $output .= "<h2>5. Fix Missing Notifications</h2>";
        $output .= "<p>If you have pending projects but no notifications, click below to generate them.</p>";
        $output .= '<form action="/debug/notifications/backfill" method="post"><button type="submit" style="padding:10px; background:blue; color:white; border:none; cursor:pointer;">Generate Notifications for Pending Projects</button></form>';

        return new Response($output);
    }

    #[Route('/debug/notifications/backfill', name: 'debug_notifications_backfill', methods: ['POST'])]
    public function backfill(
        \App\Repository\ProjetRepository $projetRepo,
        NotificationService $notificationService
    ): Response {
        $pendingProjects = $projetRepo->findBy(['statut' => 'EN_ATTENTE']);
        $count = 0;
        
        foreach ($pendingProjects as $projet) {
            try {
                $notificationService->notifyAdminNewProject($projet);
                $count++;
            } catch (\Exception $e) {
                // Ignore errors for debug
            }
        }
        
        return new Response("<h1>Generated $count notifications for pending projects.</h1><a href='/debug/notifications'>Back to Debug</a>");
    }
}