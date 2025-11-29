<?php

namespace App\Controller\Client;

use App\Repository\ProjetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client')]
#[IsGranted('ROLE_CLIENT')]
class ClientDashboardController extends AbstractController
{
    /**
     * Dashboard principal du client - Vue d'ensemble
     */
    #[Route('/dashboard', name: 'client_dashboard')]
    public function index(ProjetRepository $projetRepository): Response
    {
        $user = $this->getUser();
        
        // Récupérer tous les projets du client
        $allProjects = $projetRepository->findBy(['client' => $user], ['dateCreation' => 'DESC']);
        
        // Calculer les statistiques
        $stats = [
            'total' => count($allProjects),
            'en_attente' => count(array_filter($allProjects, fn($p) => $p->getStatut() === 'EN_ATTENTE')),
            'en_cours' => count(array_filter($allProjects, fn($p) => $p->getStatut() === 'EN_COURS')),
            'termine' => count(array_filter($allProjects, fn($p) => $p->getStatut() === 'TERMINE')),
        ];
        
        // Récupérer les 5 projets les plus récents
        $recentProjects = array_slice($allProjects, 0, 5);
        
        return $this->render('client/DashboardClient/index.html.twig', [
            'user' => $user,
            'stats' => $stats,
            'recentProjects' => $recentProjects,
        ]);
    }

    /**
     * Liste de tous les projets du client
     */
    #[Route('/projects', name: 'client_projects')]
    public function projects(Request $request, ProjetRepository $projetRepository): Response
    {
        $user = $this->getUser();
        $status = $request->query->get('status'); // Get status filter from URL
        
        // Build criteria
        $criteria = ['client' => $user];
        
        // Add status filter if provided
        if ($status) {
            $statusMap = [
                'en_attente' => 'EN_ATTENTE',
                'en_cours' => 'EN_COURS',
                'termine' => 'TERMINE',
            ];
            
            if (isset($statusMap[$status])) {
                $criteria['statut'] = $statusMap[$status];
            }
        }
        
        // Récupérer les projets filtrés
        $projects = $projetRepository->findBy($criteria, ['dateCreation' => 'DESC']);
        
        return $this->render('client/DashboardClient/project_list.html.twig', [
            'user' => $user,
            'projects' => $projects,
            'currentFilter' => $status,
        ]);
    }

    /**
     * Détail d'un projet spécifique
     */
    #[Route('/project/{id}', name: 'client_project_detail')]
    public function projectDetail(int $id, ProjetRepository $projetRepository): Response
    {
        $user = $this->getUser();
        
        // Récupérer le projet et vérifier qu'il appartient au client
        $project = $projetRepository->find($id);
        
        if (!$project || $project->getClient() !== $user) {
            throw $this->createNotFoundException('Projet non trouvé ou accès non autorisé');
        }
        
        return $this->render('client/DashboardClient/project_detail.html.twig', [
            'user' => $user,
            'project' => $project,
        ]);
    }

    /**
     * Messagerie du client
     */
    #[Route('/messages', name: 'client_messages')]
    public function messages(): Response
    {
        $user = $this->getUser();
        
        // TODO: Récupérer les conversations du client
        // $conversations = $messageRepository->findConversationsByUser($user);
        // $unreadCount = $messageRepository->countUnreadMessages($user);
        
        return $this->render('client/DashboardClient/messaging.html.twig', [
            'user' => $user,
            // 'conversations' => $conversations,
            // 'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Profil du client
     */
    #[Route('/profile', name: 'client_profile')]
    public function profile(): Response
    {
        $user = $this->getUser();
        
        // TODO: Formulaire de modification du profil
        
        return $this->render('client/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * PAGE DE TEST pour vérifier base_client.html.twig
     */
    #[Route('/test', name: 'client_test')]
    public function test(): Response
    {
        return $this->render('client/test.html.twig');
    }
}
