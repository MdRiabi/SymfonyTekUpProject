<?php

namespace App\Controller\Admin;

use App\Entity\Projet;
use App\Form\Charter\BusinessCaseType;
use App\Form\Charter\ScopeDefinitionType;
use App\Form\Charter\ResourcePlanningType;
use App\Form\Charter\DecisionType;
use App\Service\ProjectHistoryService;
use App\Service\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/project')]
class AdminProjectController extends AbstractController
{
    #[Route('/{id}/charter/business-case', name: 'admin_project_charter_step1', methods: ['GET', 'POST'])]
    public function businessCase(Projet $projet, Request $request, EntityManagerInterface $em): Response
    {
        $data = $projet->getBusinessCaseData() ?? [];
        
        $form = $this->createForm(BusinessCaseType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            
            // Save data to JSON field
            $projet->setBusinessCaseData($formData);
            
            // Check if we are validating
            if ($request->request->has('validate')) {
                if ($projet->getCharterStep() < 2) {
                    $projet->setCharterStep(2);
                }
                $projet->setStatut('EN_ANALYSE');
                $em->flush();
                
                $this->addFlash('success', '🚀 Étape 1 validée ! Passage à la Définition du Périmètre.');
                return $this->redirectToRoute('admin_project_charter_step2', ['id' => $projet->getId()]);
            }
            
            // Just saving draft
            if ($projet->getCharterStep() === null || $projet->getCharterStep() < 1) {
                $projet->setCharterStep(1);
                $projet->setStatut('EN_ANALYSE');
            }

            $em->flush();

            $this->addFlash('success', '✅ Business Case enregistré (Brouillon).');
            
            return $this->redirectToRoute('admin_project_charter_step1', ['id' => $projet->getId()]);
        }

        return $this->render('admin/project/charter/business_case.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
            'currentStep' => 1
        ]);
    }

    #[Route('/{id}/charter/scope-definition', name: 'admin_project_charter_step2', methods: ['GET', 'POST'])]
    public function scopeDefinition(Projet $projet, Request $request, EntityManagerInterface $em): Response
    {
        $data = $projet->getScopeDefinitionData() ?? [];
        
        $form = $this->createForm(ScopeDefinitionType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            
            // Save data to JSON field
            $projet->setScopeDefinitionData($formData);
            
            // Check if we are validating
            if ($request->request->has('validate')) {
                if ($projet->getCharterStep() < 3) {
                    $projet->setCharterStep(3);
                }
                $em->flush();
                
                $this->addFlash('success', '🚀 Étape 2 validée ! Passage à la Planification des Ressources.');
                return $this->redirectToRoute('admin_project_charter_step3', ['id' => $projet->getId()]); 
            }
            
            $em->flush();

            $this->addFlash('success', '✅ Définition du périmètre enregistrée (Brouillon).');
            
            return $this->redirectToRoute('admin_project_charter_step2', ['id' => $projet->getId()]);
        }

        return $this->render('admin/project/charter/scope_definition.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
            'currentStep' => 2
        ]);
    }
        
    #[Route('/{id}/charter/resource-planning', name: 'admin_project_charter_step3', methods: ['GET', 'POST'])]
    public function resourcePlanning(Projet $projet, Request $request, EntityManagerInterface $em): Response
    {
        $data = $projet->getResourcePlanningData() ?? [];
        
        $form = $this->createForm(\App\Form\Charter\ResourcePlanningType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            
            // Save data to JSON field
            $projet->setResourcePlanningData($formData);
            
            // Check if we are validating
            if ($request->request->has('validate')) {
                if ($projet->getCharterStep() < 4) {
                    $projet->setCharterStep(4);
                }
                $em->flush();
                
                $this->addFlash('success', '🚀 Étape 3 validée ! Passage à la Décision Finale.');
                return $this->redirectToRoute('admin_project_charter_step4', ['id' => $projet->getId()]); 
            }
            
            $em->flush();

            $this->addFlash('success', '✅ Planification des ressources enregistrée (Brouillon).');
            
            return $this->redirectToRoute('admin_project_charter_step3', ['id' => $projet->getId()]);
        }

        return $this->render('admin/project/charter/resource_planning.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
            'currentStep' => 3
        ]);
    }

    #[Route('/{id}/charter/decision', name: 'admin_project_charter_step4', methods: ['GET', 'POST'])]
    public function decision(Projet $projet, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(\App\Form\Charter\DecisionType::class);
        $form->handleRequest($request);

        return $this->render('admin/project/charter/decision.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
            'currentStep' => 4
        ]);
    }

    #[Route('/{id}/details', name: 'admin_project_details', methods: ['GET'])]
    public function details(Projet $projet, \App\Repository\ProjectHistoryRepository $historyRepository): Response
    {
        $history = $historyRepository->findBy(['projet' => $projet], ['createdAt' => 'DESC']);

        return $this->render('admin/project/details.html.twig', [
            'projet' => $projet,
            'history' => $history,
        ]);
    }

    #[Route('/{id}/charter/finalize/{action}', name: 'admin_project_charter_finalize', methods: ['POST'])]
    public function finalizeProject(
        Projet $projet,
        string $action,
        Request $request,
        EntityManagerInterface $em,
        ProjectHistoryService $historyService,
        \App\Service\NotificationService $notificationService
    ): Response {
        // Récupérer les remarques du formulaire
        $formData = $request->request->all('decision');
        $remarks = $formData['remarks'] ?? '';

        $oldStatus = $projet->getStatut();

        if ($action === 'go') {
            // Validation GO
            $projet->setStatut('EN_COURS');
            $projet->setCharterStep(4);
            $projet->setDateDebut(new \DateTimeImmutable());
            
            // Historique
            $historyService->log(
                $projet,
                'charter_approved',
                $remarks,
                $this->getUser(),
                $oldStatus,
                'EN_COURS'
            );
            
            $em->flush();
            
            // Notifications
            $notificationService->notifyProjectLaunch($projet);
            
            $this->addFlash('success', '🚀 Projet lancé avec succès ! Le statut est maintenant "EN COURS".');
            
            return $this->redirectToRoute('admin_project_details', ['id' => $projet->getId()]);
            
        } elseif ($action === 'no-go') {
            // Validation motif obligatoire
            if (empty($remarks)) {
                $this->addFlash('error', '❌ Le motif de refus est obligatoire.');
                return $this->redirectToRoute('admin_project_charter_step4', ['id' => $projet->getId()]);
            }
            
            // Rejet
            $projet->setStatut('REJETE');
            $projet->setRaisonRefus($remarks);
            
            // Historique
            $historyService->log(
                $projet,
                'charter_rejected',
                $remarks,
                $this->getUser(),
                $oldStatus,
                'REJETE'
            );
            
            $em->flush();
            
            // Notifications
            $notificationService->notifyProjectRejection($projet, $remarks);
            
            $this->addFlash('warning', '⚠️ Projet rejeté.');
            return $this->redirectToRoute('admin_project_details', ['id' => $projet->getId()]);
            
        } elseif ($action === 'revision') {
            // Validation remarques obligatoires
            if (empty($remarks)) {
                $this->addFlash('error', '❌ Veuillez préciser les points à réviser.');
                return $this->redirectToRoute('admin_project_charter_step4', ['id' => $projet->getId()]);
            }
            
            // Demande de révision
            $revisionRequest = [
                'date' => (new \DateTime())->format('Y-m-d H:i:s'),
                'requested_by' => $this->getUser()->getNom() . ' ' . $this->getUser()->getPrenom(),
                'remarks' => $remarks,
                'status' => 'pending'
            ];
            
            $requests = $projet->getRevisionRequests() ?? ['requests' => []];
            $requests['requests'][] = $revisionRequest;
            $projet->setRevisionRequests($requests);
            
            $projet->setStatut('EN_REVISION');
            $projet->setRevisionCount($projet->getRevisionCount() + 1);
            
            // Historique
            $historyService->log(
                $projet,
                'revision_requested',
                $remarks,
                $this->getUser(),
                $oldStatus,
                'EN_REVISION'
            );
            
            $em->flush();
            
            // Notifications
            $notificationService->notifyProjectRevision($projet, $remarks);
            
            $this->addFlash('success', '🔄 Demande de révision envoyée au client.');
            return $this->redirectToRoute('admin_project_details', ['id' => $projet->getId()]);
        }

        // Action inconnue
        $this->addFlash('error', 'Action non reconnue.');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/{id}/validate-step/{step}', name: 'admin_project_validate_step', methods: ['POST'])]
    public function validateStep(Projet $projet, int $step, EntityManagerInterface $em): Response
    {
        // Legacy method kept for safety, but logic is now in the steps themselves
        if ($step === 1) {
            if (empty($projet->getBusinessCaseData())) {
                $this->addFlash('error', '❌ Veuillez remplir le Business Case avant de valider.');
                return $this->redirectToRoute('admin_project_charter_step1', ['id' => $projet->getId()]);
            }
            
            if ($projet->getCharterStep() < 2) {
                $projet->setCharterStep(2);
            }
            $em->flush();
            
            $this->addFlash('success', '🚀 Étape 1 validée ! Passage à la Définition du Périmètre.');
            return $this->redirectToRoute('admin_project_charter_step2', ['id' => $projet->getId()]);
        }
        
        if ($step === 2) {
            if (empty($projet->getScopeDefinitionData())) {
                $this->addFlash('error', '❌ Veuillez définir le périmètre avant de valider.');
                return $this->redirectToRoute('admin_project_charter_step2', ['id' => $projet->getId()]);
            }
            
            if ($projet->getCharterStep() < 3) {
                $projet->setCharterStep(3);
            }
            $em->flush();
            
            $this->addFlash('success', '🚀 Étape 2 validée ! Passage à la Planification des Ressources.');
            return $this->redirectToRoute('admin_project_charter_step3', ['id' => $projet->getId()]);
        }

        if ($step === 3) {
            if (empty($projet->getResourcePlanningData())) {
                $this->addFlash('error', '❌ Veuillez planifier les ressources avant de valider.');
                return $this->redirectToRoute('admin_project_charter_step3', ['id' => $projet->getId()]);
            }
            
            if ($projet->getCharterStep() < 4) {
                $projet->setCharterStep(4);
            }
            $em->flush();
            
            $this->addFlash('success', '🚀 Étape 3 validée ! Passage à la Décision Finale.');
            return $this->redirectToRoute('admin_project_charter_step4', ['id' => $projet->getId()]);
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * Liste tous les projets configurés
     */
    #[Route('/projects', name: 'admin_projects_list', methods: ['GET'])]
    public function listProjects(ProjectService $projectService): Response
    {
        $projects = $projectService->getAllConfiguredProjects();
        $stats = $projectService->getProjectStats();

        return $this->render('admin/project/projects_list.html.twig', [
            'projects' => $projects,
            'stats' => $stats,
            'filter' => 'all',
        ]);
    }

    /**
     * Liste des projets actifs
     */
    #[Route('/projects/active', name: 'admin_projects_active', methods: ['GET'])]
    public function listActiveProjects(ProjectService $projectService): Response
    {
        $projects = $projectService->getActiveProjects();
        $stats = $projectService->getProjectStats();

        return $this->render('admin/project/projects_list.html.twig', [
            'projects' => $projects,
            'stats' => $stats,
            'filter' => 'active',
        ]);
    }

    /**
     * Liste des projets terminés
     */
    #[Route('/projects/completed', name: 'admin_projects_completed', methods: ['GET'])]
    public function listCompletedProjects(ProjectService $projectService): Response
    {
        $projects = $projectService->getCompletedProjects();
        $stats = $projectService->getProjectStats();

        return $this->render('admin/project/projects_list.html.twig', [
            'projects' => $projects,
            'stats' => $stats,
            'filter' => 'completed',
        ]);
    }
}
