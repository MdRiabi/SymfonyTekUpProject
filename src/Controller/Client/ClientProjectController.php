<?php

namespace App\Controller\Client;

use App\Entity\Projet;
use App\Form\ProjectSubmissionType;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client/projects')]
#[IsGranted('ROLE_CLIENT')]
class ClientProjectController extends AbstractController
{
    #[Route('/new', name: 'client_submit_project', methods: ['GET', 'POST'])]
    public function submitProject(
        Request $request,
        EntityManagerInterface $em,
        NotificationService $notificationService
    ): Response {
        $projet = new Projet();
        $form = $this->createForm(ProjectSubmissionType::class, $projet);
        $form->handleRequest($request);

        // Debug: Check if form is submitted
        if ($request->isMethod('POST')) {
            $this->addFlash('info', 'Formulaire soumis - Traitement en cours...');
        }

        if ($form->isSubmitted()) {
            $this->addFlash('info', 'Formulaire détecté comme soumis');
            
            if (!$form->isValid()) {
                // Show validation errors
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                $this->addFlash('error', 'Erreurs de validation: ' . implode(', ', $errors));
                
                return $this->render('client/DashboardClient/project_submission.html.twig', [
                    'form' => $form->createView()
                ]);
            }
            
            try {
                // Récupérer les fonctionnalités depuis le formulaire (JavaScript)
                $fonctionnalitesJson = $request->request->get('fonctionnalites_json', '[]');
                $fonctionnalites = json_decode($fonctionnalitesJson, true) ?: [];
                
                // Gérer l'upload du fichier
                $fichierJoint = $form->get('fichierJoint')->getData();
                if ($fichierJoint) {
                    $this->addFlash('info', 'Fichier détecté: ' . $fichierJoint->getClientOriginalName());
                    $originalFilename = pathinfo($fichierJoint->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $this->sanitizeFilename($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$fichierJoint->guessExtension();

                    try {
                        $uploadDir = $this->getParameter('projet_uploads_directory');
                        
                        // Create directory if it doesn't exist
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        $fichierJoint->move($uploadDir, $newFilename);
                        $projet->setFichierJoint($newFilename);
                        $this->addFlash('success', 'Fichier uploadé avec succès: ' . $newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload du fichier: ' . $e->getMessage());
                    }
                }
                
                $projet->setClient($this->getUser());
                $projet->setStatut('EN_ATTENTE');
                $projet->setFonctionnalites($fonctionnalites);
                $projet->setDateCreation(new \DateTimeImmutable());
                $projet->setDateModification(new \DateTimeImmutable());
                
                $em->persist($projet);
                $em->flush();
                
                $this->addFlash('success', 'Projet enregistré en base de données avec ID: ' . $projet->getId());
                
                // Notifier tous les admins
                try {
                    $notificationService->notifyAdminNewProject($projet);
                    $this->addFlash('success', 'Notifications envoyées aux administrateurs');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Projet créé mais erreur lors de l\'envoi des notifications: ' . $e->getMessage());
                }
                
                $this->addFlash('success', '✅ Votre projet a été soumis avec succès ! Nous vous répondrons dans les 24 heures.');
                return $this->redirectToRoute('client_dashboard');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la soumission: ' . $e->getMessage());
                return $this->render('client/DashboardClient/project_submission.html.twig', [
                    'form' => $form->createView()
                ]);
            }
        }

        return $this->render('client/DashboardClient/project_submission.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/edit', name: 'client_edit_project', methods: ['GET', 'POST'])]
    public function editProject(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $projet = $em->getRepository(Projet::class)->find($id);
        
        // Vérifier que le projet existe et appartient au client
        if (!$projet || $projet->getClient() !== $this->getUser()) {
            $this->addFlash('error', '❌ Projet non trouvé ou accès non autorisé');
            return $this->redirectToRoute('client_projects');
        }
        
        // Use ProjectEditType for optional fields
        $form = $this->createForm(\App\Form\ProjectEditType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    // Récupérer les fonctionnalités depuis le formulaire (JavaScript)
                    $fonctionnalitesJson = $request->request->get('fonctionnalites_json', '[]');
                    $fonctionnalites = json_decode($fonctionnalitesJson, true) ?: [];
                    
                    // Only update features if provided
                    if (!empty($fonctionnalites)) {
                        $projet->setFonctionnalites($fonctionnalites);
                    }
                    
                    // Gérer l'upload du fichier
                    $fichierJoint = $form->get('fichierJoint')->getData();
                    if ($fichierJoint) {
                        $originalFilename = pathinfo($fichierJoint->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $this->sanitizeFilename($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$fichierJoint->guessExtension();

                        try {
                            $uploadDir = $this->getParameter('projet_uploads_directory');
                            
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }
                            
                            $fichierJoint->move($uploadDir, $newFilename);
                            $projet->setFichierJoint($newFilename);
                            $this->addFlash('success', '📎 Fichier uploadé avec succès');
                        } catch (\Exception $e) {
                            $this->addFlash('warning', '⚠️ Erreur lors de l\'upload du fichier: ' . $e->getMessage());
                        }
                    }
                    
                    $projet->setDateModification(new \DateTimeImmutable());
                    
                    $em->flush();
                    
                    $this->addFlash('success', '✅ Votre projet a été modifié avec succès !');
                    return $this->redirectToRoute('client_project_detail', ['id' => $projet->getId()]);
                    
                } catch (\Exception $e) {
                    $this->addFlash('error', '❌ Erreur lors de la modification: ' . $e->getMessage());
                }
            } else {
                // Show validation errors
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                if (!empty($errors)) {
                    $this->addFlash('error', '❌ Erreurs de validation: ' . implode(', ', $errors));
                }
            }
        }

        return $this->render('client/DashboardClient/project_submission.html.twig', [
            'form' => $form->createView(),
            'projet' => $projet,
            'isEdit' => true,
        ]);
    }

    /**
     * Sanitize filename to create safe web-compatible filenames
     */
    private function sanitizeFilename(string $filename): string
    {
        // Convert to lowercase
        $filename = strtolower($filename);
        
        // Replace non-alphanumeric characters with underscores
        $filename = preg_replace('/[^a-z0-9_.-]/', '_', $filename);
        
        // Remove multiple consecutive underscores
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Remove leading/trailing underscores
        $filename = trim($filename, '_');
        
        // Return the sanitized filename
        return $filename ?: 'file';
    }

    #[Route('/', name: 'client_projects', methods: ['GET'])]
    public function myProjects(EntityManagerInterface $em): Response
    {
        $projets = $em->getRepository(Projet::class)->findBy(
            ['client' => $this->getUser()],
            ['dateCreation' => 'DESC']
        );

        return $this->render('client/my_projects.html.twig', [
            'projets' => $projets,
        ]);
    }

    #[Route('/{id}', name: 'client_project_detail', methods: ['GET'])]
    public function projectDetails(
        int $id, 
        EntityManagerInterface $em,
        \App\Repository\ProjectHistoryRepository $historyRepository
    ): Response {
        $projet = $em->getRepository(Projet::class)->find($id);

        if (!$projet || $projet->getClient() !== $this->getUser()) {
            $this->addFlash('error', 'Projet non trouvé ou accès refusé.');
            return $this->redirectToRoute('client_projects');
        }

        $history = $historyRepository->findBy(['projet' => $projet], ['createdAt' => 'DESC']);

        return $this->render('client/project_details.html.twig', [
            'projet' => $projet,
            'history' => $history,
        ]);
    }
}
