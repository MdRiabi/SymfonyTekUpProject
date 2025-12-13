<?php

namespace App\Controller\TeamMember;

use App\Service\TeamTaskService;
use App\Service\TimeTrackingService;
use App\Service\TaskNoteService;
use App\Service\TaskAttachmentService;
use App\Repository\TacheRepository;
use App\Repository\TaskNoteRepository;
use App\Repository\TaskAttachmentRepository;
use App\Repository\TaskCommentRepository;
use App\Form\TaskNoteType;
use App\Form\TaskAttachmentType;
use App\Form\TaskCommentType;
use App\Entity\TaskComment;
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
        TimeTrackingService $timeTracking,
        TaskNoteRepository $taskNoteRepo,
        TaskAttachmentRepository $taskAttachmentRepo,
        TaskCommentRepository $taskCommentRepo
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

        // Récupérer les notes, pièces jointes et commentaires
        $notes = $taskNoteRepo->findByTask($task);
        $attachments = $taskAttachmentRepo->findByTask($task);
        $comments = $taskCommentRepo->findByTask($task);

        // Créer les formulaires
        $noteForm = $this->createForm(TaskNoteType::class);
        $attachmentForm = $this->createForm(TaskAttachmentType::class);
        $commentForm = $this->createForm(TaskCommentType::class);

        return $this->render('team_member/tasks/task_detail.html.twig', [
            'task' => $task,
            'activeTimer' => $activeTimer,
            'hasActiveTimer' => $hasActiveTimer,
            'totalTime' => $timeTracking->formatDuration($totalTime),
            'totalTimeSeconds' => $totalTime,
            'timeSessions' => $timeSessions,
            'notes' => $notes,
            'attachments' => $attachments,
            'comments' => $comments,
            'noteForm' => $noteForm->createView(),
            'attachmentForm' => $attachmentForm->createView(),
            'commentForm' => $commentForm->createView(),
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

    /**
     * Ajouter une note de travail
     */
    #[Route('/tasks/{id}/notes', name: 'team_task_add_note', methods: ['POST'])]
    public function addNote(
        int $id,
        Request $request,
        TacheRepository $tacheRepo,
        TaskNoteService $taskNoteService
    ): Response {
        $user = $this->getUser();
        $task = $tacheRepo->find($id);

        if (!$task || $task->getAssigne() !== $user) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }

        $form = $this->createForm(TaskNoteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->get('contenu')->getData();
            $taskNoteService->createNote($task, $user, $content);
            
            $this->addFlash('success', 'Note ajoutée avec succès');
        }

        return $this->redirectToRoute('team_task_detail', ['id' => $id]);
    }

    /**
     * Uploader une pièce jointe
     */
    #[Route('/tasks/{id}/attachments', name: 'team_task_upload_attachment', methods: ['POST'])]
    public function uploadAttachment(
        int $id,
        Request $request,
        TacheRepository $tacheRepo,
        TaskAttachmentService $taskAttachmentService
    ): Response {
        $user = $this->getUser();
        $task = $tacheRepo->find($id);

        if (!$task || $task->getAssigne() !== $user) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }

        $form = $this->createForm(TaskAttachmentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            
            if ($file) {
                try {
                    $taskAttachmentService->uploadFile($task, $user, $file);
                    $this->addFlash('success', 'Fichier téléchargé avec succès');
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return $this->redirectToRoute('team_task_detail', ['id' => $id]);
    }

    /**
     * Supprimer une pièce jointe
     */
    #[Route('/tasks/{taskId}/attachments/{attachmentId}', name: 'team_task_delete_attachment', methods: ['POST'])]
    public function deleteAttachment(
        int $taskId,
        int $attachmentId,
        TacheRepository $tacheRepo,
        TaskAttachmentRepository $taskAttachmentRepo,
        TaskAttachmentService $taskAttachmentService
    ): Response {
        $user = $this->getUser();
        $task = $tacheRepo->find($taskId);
        $attachment = $taskAttachmentRepo->find($attachmentId);

        if (!$task || !$attachment || $attachment->getTache() !== $task) {
            throw $this->createNotFoundException('Fichier non trouvé');
        }

        if (!$taskAttachmentService->canDelete($attachment, $user)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce fichier');
        }

        $taskAttachmentService->deleteFile($attachment);
        $this->addFlash('success', 'Fichier supprimé avec succès');

        return $this->redirectToRoute('team_task_detail', ['id' => $taskId]);
    }

    /**
     * Ajouter un commentaire
     */
    #[Route('/tasks/{id}/comments', name: 'team_task_add_comment', methods: ['POST'])]
    public function addComment(
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

        $comment = new TaskComment();
        $form = $this->createForm(TaskCommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setTache($task);
            $comment->setAuteur($user);
            
            $em->persist($comment);
            $em->flush();
            
            $this->addFlash('success', 'Commentaire ajouté avec succès');
        }

        return $this->redirectToRoute('team_task_detail', ['id' => $id]);
    }
}
