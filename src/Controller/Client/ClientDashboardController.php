<?php

namespace App\Controller\Client;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(): Response
    {
        $user = $this->getUser();
        
        // TODO: Récupérer les statistiques du client
        // - Nombre de projets actifs
        // - Nombre de projets terminés
        // - Messages non lus
        // - Projets en attente de validation
        
        return $this->render('client/DashboardClient/index.html.twig', [
            'user' => $user,
            // 'stats' => $stats,
            // 'recentProjects' => $recentProjects,
            // 'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Liste de tous les projets du client
     */
    #[Route('/projects', name: 'client_projects')]
    public function projects(): Response
    {
        $user = $this->getUser();
        
        // TODO: Récupérer les projets du client
        // $projects = $projectRepository->findByClient($user);
        
        return $this->render('client/DashboardClient/project_list.html.twig', [
            'user' => $user,
            // 'projects' => $projects,
        ]);
    }

    /**
     * Détail d'un projet spécifique
     */
    #[Route('/project/{id}', name: 'client_project_detail')]
    public function projectDetail(int $id): Response
    {
        $user = $this->getUser();
        
        // TODO: Récupérer le projet et vérifier qu'il appartient au client
        // $project = $projectRepository->find($id);
        // if (!$project || $project->getClient() !== $user) {
        //     throw $this->createNotFoundException('Projet non trouvé');
        // }
        
        return $this->render('client/DashboardClient/project_detail.html.twig', [
            'user' => $user,
            // 'project' => $project,
            // 'phases' => $project->getPhases(),
            // 'team' => $project->getTeam(),
            // 'activities' => $project->getActivities(),
        ]);
    }

    /**
     * Formulaire de soumission d'un nouveau projet
     */
    #[Route('/submit-project', name: 'client_submit_project')]
    public function submitProject(): Response
    {
        $user = $this->getUser();
        
        // TODO: Créer le formulaire de soumission
        // $form = $this->createForm(ProjectSubmissionType::class);
        // $form->handleRequest($request);
        
        // if ($form->isSubmitted() && $form->isValid()) {
        //     // Enregistrer le projet
        //     $this->addFlash('success', 'Votre projet a été soumis avec succès !');
        //     return $this->redirectToRoute('client_projects');
        // }
        
        return $this->render('client/DashboardClient/project_submission.html.twig', [
            'user' => $user,
            // 'form' => $form->createView(),
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
