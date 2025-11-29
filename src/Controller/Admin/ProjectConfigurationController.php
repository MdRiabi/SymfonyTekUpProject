<?php

namespace App\Controller\Admin;

use App\Entity\Phase;
use App\Entity\Projet;
use App\Entity\Tache;
use App\Form\PhaseType;
use App\Form\TacheType;
use App\Service\ProjectHistoryService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/project/{id}/configuration', name: 'admin_project_configuration_')]
class ProjectConfigurationController extends AbstractController
{
    #[Route('/', name: 'dashboard', methods: ['GET'])]
    public function dashboard(Projet $projet): Response
    {
        return $this->render('admin/project/configuration/dashboard.html.twig', [
            'projet' => $projet,
        ]);
    }

    #[Route('/phases', name: 'phases', methods: ['GET', 'POST'])]
    public function managePhases(Projet $projet, Request $request, EntityManagerInterface $em): Response
    {
        $phase = new Phase();
        $phase->setProjet($projet);
        
        // Simple form handling manually for now or use a FormType if complex
        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $description = $request->request->get('description');
            $dateDebut = $request->request->get('dateDebut');
            $dateFin = $request->request->get('dateFin');

            if ($nom) {
                $phase->setNom($nom);
                $phase->setDescription($description);
                if ($dateDebut) $phase->setDateDebut(new \DateTimeImmutable($dateDebut));
                if ($dateFin) $phase->setDateFin(new \DateTimeImmutable($dateFin));
                
                $em->persist($phase);
                $em->flush();
                
                $this->addFlash('success', 'Phase ajoutée avec succès.');
                return $this->redirectToRoute('admin_project_configuration_phases', ['id' => $projet->getId()]);
            }
        }

        return $this->render('admin/project/configuration/phases.html.twig', [
            'projet' => $projet,
            'phases' => $projet->getPhases(),
        ]);
    }

    #[Route('/phases/{phaseId}/delete', name: 'phase_delete', methods: ['POST'])]
    public function deletePhase(Projet $projet, int $phaseId, EntityManagerInterface $em): Response
    {
        $phase = $em->getRepository(Phase::class)->find($phaseId);
        if ($phase && $phase->getProjet() === $projet) {
            $em->remove($phase);
            $em->flush();
            $this->addFlash('success', 'Phase supprimée.');
        }

        return $this->redirectToRoute('admin_project_configuration_phases', ['id' => $projet->getId()]);
    }

    #[Route('/tasks', name: 'tasks', methods: ['GET', 'POST'])]
    public function manageTasks(Projet $projet, Request $request, EntityManagerInterface $em): Response
    {
        // This would ideally use a proper Symfony Form, but for speed/simplicity in this iteration:
        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $phaseId = $request->request->get('phase_id');
            
            if ($nom && $phaseId) {
                $phase = $em->getRepository(Phase::class)->find($phaseId);
                if ($phase && $phase->getProjet() === $projet) {
                    $tache = new Tache();
                    $tache->setNom($nom);
                    $tache->setPhase($phase);
                    $tache->setProjet($projet);
                    $tache->setCreateur($this->getUser());
                    $tache->setStatut('A_FAIRE');
                    $tache->setDateDebut(new \DateTimeImmutable());
                    
                    $em->persist($tache);
                    $em->flush();
                    
                    $this->addFlash('success', 'Tâche ajoutée avec succès.');
                    return $this->redirectToRoute('admin_project_configuration_tasks', ['id' => $projet->getId()]);
                }
            }
        }

        return $this->render('admin/project/configuration/tasks.html.twig', [
            'projet' => $projet,
            'phases' => $projet->getPhases(), // To group tasks by phase
        ]);
    }

    #[Route('/assign', name: 'assign', methods: ['GET', 'POST'])]
    public function assignAndPlan(Projet $projet, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $assignments = $request->request->all('assignments');
            
            foreach ($assignments as $tacheId => $data) {
                $tache = $em->getRepository(Tache::class)->find($tacheId);
                if ($tache && $tache->getProjet() === $projet) {
                    // Assign user
                    if (!empty($data['assigne_id'])) {
                        $user = $em->getRepository(\App\Entity\Utilisateur::class)->find($data['assigne_id']);
                        $tache->setAssigne($user);
                    }
                    
                    // Set priority
                    if (!empty($data['priorite'])) {
                        $tache->setPriorite($data['priorite']);
                    }
                    
                    // Set deadline
                    if (!empty($data['deadline'])) {
                        $tache->setDeadline(new \DateTimeImmutable($data['deadline']));
                    }
                    
                    // Set estimated hours
                    if (!empty($data['estimated_hours'])) {
                        $tache->setEstimatedHours((int)$data['estimated_hours']);
                    }
                }
            }
            
            $em->flush();
            $this->addFlash('success', 'Assignations enregistrées avec succès.');
            return $this->redirectToRoute('admin_project_configuration_assign', ['id' => $projet->getId()]);
        }

        // Get all users for assignment dropdown
        $users = $em->getRepository(\App\Entity\Utilisateur::class)->findAll();

        return $this->render('admin/project/configuration/assign.html.twig', [
            'projet' => $projet,
            'phases' => $projet->getPhases(),
            'users' => $users,
        ]);
    }

    #[Route('/finalize', name: 'finalize', methods: ['POST'])]
    public function finalizeConfiguration(
        Projet $projet,
        Request $request,
        EntityManagerInterface $em,
        ProjectHistoryService $historyService,
        NotificationService $notificationService
    ): Response {
        // Validate project is ready to finalize
        $validation = $projet->isReadyToFinalize();
        
        if (!$validation['ready']) {
            foreach ($validation['errors'] as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('admin_project_configuration_dashboard', ['id' => $projet->getId()]);
        }

        // Get custom message if provided
        $customMessage = $request->request->get('custom_message', '');

        // Update project status
        $oldStatus = $projet->getStatut();
        $projet->setStatut(Projet::STATUT_CONFIGURE);
        $projet->setDateModification(new \DateTimeImmutable());

        // Save changes
        $em->flush();

        // Log history
        $historyService->log(
            $projet,
            'configuration_finalized',
            sprintf(
                'Configuration finalisée : %d phase(s), %d tâche(s), %d assignée(s)',
                $validation['stats']['phases'],
                $validation['stats']['tasks'],
                $validation['stats']['assigned']
            ),
            $this->getUser(),
            $oldStatus,
            Projet::STATUT_CONFIGURE
        );

        // Get all assigned members
        $assignedMembers = [];
        foreach ($projet->getTaches() as $tache) {
            if ($tache->getAssigne() && !in_array($tache->getAssigne(), $assignedMembers, true)) {
                $assignedMembers[] = $tache->getAssigne();
            }
        }

        // Send notifications
        $notificationService->notifyConfigurationFinalized(
            $projet,
            $assignedMembers,
            $customMessage
        );

        $this->addFlash('success', 'Configuration finalisée avec succès ! Le projet est maintenant actif.');
        
        // Redirect to 360° dashboard
        return $this->redirectToRoute('admin_project_dashboard_360', ['id' => $projet->getId()]);
    }
}
