<?php

namespace App\Controller\TeamMember;

use App\Service\TeamTaskService;
use App\Service\TimeTrackingService;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/team')]
#[IsGranted('ROLE_USER')]
class TeamDashboardController extends AbstractController
{
    /**
     * Dashboard personnel du membre d'équipe
     */
    #[Route('/dashboard', name: 'team_dashboard', methods: ['GET'])]
    public function dashboard(TeamTaskService $teamTaskService): Response
    {
        $user = $this->getUser();
        
        // Récupérer les KPIs personnels
        $kpis = $teamTaskService->getPersonalKPIs($user);
        
        // Récupérer mes projets
        $projects = $teamTaskService->getMyProjects($user);

        return $this->render('team_member/dashboard.html.twig', [
            'kpis' => $kpis,
            'projects' => $projects,
            'user' => $user,
        ]);
    }

    /**
     * Liste de mes tâches
     */
    #[Route('/tasks', name: 'team_tasks', methods: ['GET'])]
    public function myTasks(Request $request, TeamTaskService $teamTaskService): Response
    {
        $user = $this->getUser();
        
        // Récupérer les filtres depuis la requête
        $filters = [
            'statut' => $request->query->get('statut', 'all'),
            'projet' => $request->query->get('projet'),
            'priorite' => $request->query->get('priorite', 'all'),
            'orderBy' => $request->query->get('orderBy', 'deadline'),
            'order' => $request->query->get('order', 'ASC'),
        ];

        // Récupérer les tâches filtrées
        $tasks = $teamTaskService->getMyTasksFiltered($user, $filters);
        
        // Récupérer mes projets pour le filtre
        $projects = $teamTaskService->getMyProjects($user);

        return $this->render('team_member/tasks/my_tasks.html.twig', [
            'tasks' => $tasks,
            'projects' => $projects,
            'filters' => $filters,
            'user' => $user,
        ]);
    }

    /**
     * Détail d'une tâche
     */
    #[Route('/tasks/{id}', name: 'team_task_detail', methods: ['GET'])]
    public function taskDetail(
        int $id,
        TacheRepository $tacheRepo,
        TimeTrackingService $timeTracking
    ): Response {
        $user = $this->getUser();
        $task = $tacheRepo->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }

        // Vérifier que la tâche est assignée à l'utilisateur
        if ($task->getAssigne() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas assigné à cette tâche');
        }

        // Récupérer les données du timer
        $activeTimer = $timeTracking->getActiveTimer($user);
        $totalTime = $timeTracking->getTotalTimeOnTask($task, $user);
        $timeSessions = $timeTracking->getTimeSessions($task, $user);
        $hasActiveTimer = $activeTimer && $activeTimer->getTache()->getId() === $task->getId();

        return $this->render('team_member/tasks/task_detail.html.twig', [
            'task' => $task,
            'activeTimer' => $activeTimer,
            'hasActiveTimer' => $hasActiveTimer,
            'totalTime' => $timeTracking->formatDuration($totalTime),
            'totalTimeSeconds' => $totalTime,
            'timeSessions' => $timeSessions,
            'user' => $user,
        ]);
    }

    /**
     * Démarrer le timer
     */
    #[Route('/tasks/{id}/timer/start', name: 'team_task_timer_start', methods: ['POST'])]
    public function startTimer(
        int $id,
        TacheRepository $tacheRepo,
        TimeTrackingService $timeTracking
    ): JsonResponse {
        $user = $this->getUser();
        $task = $tacheRepo->find($id);

        if (!$task || $task->getAssigne() !== $user) {
            return new JsonResponse(['error' => 'Tâche non trouvée ou non assignée'], 404);
        }

        try {
            $entry = $timeTracking->startTimer($task, $user);
            
            return new JsonResponse([
                'success' => true,
                'timer_id' => $entry->getId(),
                'start_time' => $entry->getDateDebut()->format('Y-m-d H:i:s'),
                'message' => 'Timer démarré avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Arrêter le timer
     */
    #[Route('/tasks/{id}/timer/stop', name: 'team_task_timer_stop', methods: ['POST'])]
    public function stopTimer(
        int $id,
        TacheRepository $tacheRepo,
        TimeTrackingService $timeTracking
    ): JsonResponse {
        $user = $this->getUser();
        $task = $tacheRepo->find($id);

        if (!$task || $task->getAssigne() !== $user) {
            return new JsonResponse(['error' => 'Tâche non trouvée'], 404);
        }

        $entry = $timeTracking->stopTimer($user);

        if (!$entry) {
            return new JsonResponse(['error' => 'Aucun timer actif'], 400);
        }

        $totalTime = $timeTracking->getTotalTimeOnTask($task, $user);

        return new JsonResponse([
            'success' => true,
            'duration' => $entry->getDuree(),
            'duration_formatted' => $entry->getDureeFormatee(),
            'total_time' => $totalTime,
            'total_time_formatted' => $timeTracking->formatDuration($totalTime),
            'message' => 'Timer arrêté avec succès'
        ]);
    }

    /**
     * Mettre à jour le statut de la tâche
     */
    #[Route('/tasks/{id}/status', name: 'team_task_update_status', methods: ['POST'])]
    public function updateStatus(
        int $id,
        Request $request,
        TacheRepository $tacheRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $task = $tacheRepo->find($id);

        if (!$task || $task->getAssigne() !== $user) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }

        $newStatus = $request->request->get('statut');
        
        // Valider le statut
        $validStatuses = ['A_FAIRE', 'EN_COURS', 'TERMINE'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->addFlash('error', 'Statut invalide');
            return $this->redirectToRoute('team_task_detail', ['id' => $id]);
        }

        $task->setStatut($newStatus);
        $em->flush();

        $this->addFlash('success', 'Statut mis à jour avec succès');
        return $this->redirectToRoute('team_task_detail', ['id' => $id]);
    }
}
