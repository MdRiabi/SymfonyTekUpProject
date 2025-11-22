<?php namespace App\Controller\Admin;

use App\Repository\UtilisateurRepository;
use App\Repository\RoleRepository;
use App\Repository\AccountRequestRepository;
use App\Form\Admin\UserCreationType;
use App\Form\Admin\UserEditType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Utilisateur;
use App\Entity\AccountRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AdminUserManagementController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_manage_users')]
    public function list(UtilisateurRepository $utilisateurRepository): Response
    {
        $users = $utilisateurRepository->findAll();

        return $this->render('admin/users/list.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/create', name: 'admin_create_user')]
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new Utilisateur();
        $form = $this->createForm(UserCreationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Set creation date
            $user->setDateCreation(new \DateTimeImmutable());

            // Persist and flush
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès !');

            return $this->redirectToRoute('admin_manage_users');
        }

        return $this->render('admin/users/createUser.html.twig', [
            'userCreationForm' => $form->createView(),
        ]);
    }


    #[Route('/admin/users/{id}/edit-data', name: 'admin_user_edit_data', methods: ['GET'])]
    public function getUserEditData(Utilisateur $user): Response
    {
        return $this->json([
            'id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'adresse' => $user->getAdresse(),
            'titrePoste' => $user->getTitrePoste(),
            'departement' => $user->getDepartement(),
            'equipe' => $user->getEquipe(),
            'matricule' => $user->getMatricule(),
            'role' => $user->getRole()?->getId(),
            'estActif' => $user->isEstActif(),
            'capaciteHebdoH' => $user->getCapaciteHebdoH(),
            'competences' => $user->getCompetences(),
            'message' => $user->getMessage(),
            'manager' => $user->getManager()?->getId(),
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'admin_user_edit', methods: ['POST'])]
    public function editUser(
        Utilisateur $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update timestamp
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès !');

            return $this->redirectToRoute('admin_manage_users');
        }

        // If form has errors, return them as JSON
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json(['errors' => $errors], 400);
    }

    #[Route('/admin/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(
        Utilisateur $user,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $userName = $user->getPrenom() . ' ' . $user->getNom();
            
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', "L'utilisateur {$userName} a été supprimé avec succès !");

            return $this->redirectToRoute('admin_manage_users');
        } catch (\Exception $e) {
            $this->addFlash('error', "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
            return $this->redirectToRoute('admin_manage_users');
        }
    }


    #[Route('/admin', name: 'app_admin')]
    public function manage(
        UtilisateurRepository $utilisateurRepository,
        RoleRepository $roleRepository,
        AccountRequestRepository $accountRequestRepository
    ): Response {
        // On utilise les repositories pour compter les entités
        $totalUsersCount = $utilisateurRepository->count([]);
        $totalRolesCount = $roleRepository->count([]);
        $pendingRequestsCount = $accountRequestRepository->count(['status' => AccountRequest::STATUS_PENDING]);

        return $this->render('admin/users.html.twig', [
            'total_users_count' => $totalUsersCount,
            'total_roles_count' => $totalRolesCount,
            'pending_requests_count' => $pendingRequestsCount,
        ]);
    }


}