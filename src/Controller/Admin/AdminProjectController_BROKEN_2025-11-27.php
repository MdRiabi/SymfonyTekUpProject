<?php

namespace App\Controller\Admin;

use App\Entity\Projet;
use App\Form\Charter\BusinessCaseType;
use App\Form\Charter\ScopeDefinitionType;
use App\Form\Charter\ResourcePlanningType;
use App\Form\Charter\DecisionType;
use App\Service\ProjectHistoryService;
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
}