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
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\UserSession;
use App\Entity\UserNotificationSetting;
use App\Form\Admin\NotificationSettingType;

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

    #[Route('/admin/test-translation', name: 'admin_test_translation')]
    public function testTranslation(): Response
    {
        return $this->render('admin/test_translation.html.twig');
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
        EntityManagerInterface $entityManager,
        RoleRepository $roleRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        try {
            // Get form data
            $data = $request->request->all('user_edit_type');
            
            // Update user properties
            if (isset($data['nom'])) $user->setNom($data['nom']);
            if (isset($data['prenom'])) $user->setPrenom($data['prenom']);
            if (isset($data['email'])) $user->setEmail($data['email']);
            if (isset($data['adresse'])) $user->setAdresse($data['adresse']);
            if (isset($data['titrePoste'])) $user->setTitrePoste($data['titrePoste']);
            if (isset($data['departement'])) $user->setDepartement($data['departement']);
            if (isset($data['equipe'])) $user->setEquipe($data['equipe']);
            if (isset($data['matricule'])) $user->setMatricule($data['matricule']);
            if (isset($data['capaciteHebdoH'])) $user->setCapaciteHebdoH($data['capaciteHebdoH']);
            if (isset($data['message'])) $user->setMessage($data['message']);
            
            // Update role
            if (isset($data['role'])) {
                $role = $roleRepository->find($data['role']);
                if ($role) $user->setRole($role);
            }
            
            // Update manager
            if (isset($data['manager']) && !empty($data['manager'])) {
                $manager = $utilisateurRepository->find($data['manager']);
                $user->setManager($manager);
            } else {
                $user->setManager(null);
            }
            
            // Update competences
            if (isset($data['competences'])) {
                $user->setCompetences($data['competences']);
            }
            
            // Update estActif
            $user->setEstActif(isset($data['estActif']));
            
            // Update timestamp
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès !');

            return $this->redirectToRoute('admin_manage_users');
            
        } catch (\Exception $e) {
            return $this->json(['errors' => [$e->getMessage()]], 400);
        }
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

    #[Route('/admin/profile', name: 'admin_profile', methods: ['GET'])]
    public function profile(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Sessions
        $sessions = $entityManager->getRepository(UserSession::class)->findBy(
            ['user' => $user],
            ['lastActiveAt' => 'DESC']
        );

        // Notifications
        $notificationSetting = $user->getNotificationSetting();
        if (!$notificationSetting) {
            $notificationSetting = new UserNotificationSetting();
            // Don't persist here, just for form display default values
        }
        $notificationForm = $this->createForm(NotificationSettingType::class, $notificationSetting, [
            'action' => $this->generateUrl('admin_user_update_notifications'),
            'method' => 'POST'
        ]);

        return $this->render('admin/users/profile.html.twig', [
            'sessions' => $sessions,
            'notificationForm' => $notificationForm->createView()
        ]);
    }

    #[Route('/admin/profile/update', name: 'admin_profile_update', methods: ['POST'])]
    public function updateProfile(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        try {
            // Update user properties
            if ($request->request->has('prenom')) {
                $user->setPrenom($request->request->get('prenom'));
            }
            if ($request->request->has('nom')) {
                $user->setNom($request->request->get('nom'));
            }
            if ($request->request->has('email')) {
                $user->setEmail($request->request->get('email'));
            }
            if ($request->request->has('adresse')) {
                $user->setAdresse($request->request->get('adresse'));
            }
            if ($request->request->has('titrePoste')) {
                $user->setTitrePoste($request->request->get('titrePoste'));
            }

            // Update timestamp
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');

            return $this->redirectToRoute('admin_profile');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour du profil : ' . $e->getMessage());
            return $this->redirectToRoute('admin_profile');
        }
    }

    #[Route('/admin/profile/update-language', name: 'admin_user_update_language', methods: ['POST'])]
    public function updateLanguage(
        Request $request,
        EntityManagerInterface $entityManager,
        \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
    ): Response {
        $user = $this->getUser();
        
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException();
        }

        // Verify CSRF token
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('update_language', $submittedToken)) {
            $this->addFlash('error', 'flash.error.invalid_csrf');
            return $this->redirectToRoute('admin_profile');
        }

        try {
            $language = $request->request->get('language');
            
            // Validate language
            $allowedLanguages = ['fr', 'en', 'it', 'ar', 'es', 'de'];
            if (!in_array($language, $allowedLanguages)) {
                throw new \InvalidArgumentException('flash.error.invalid_language');
            }

            // Update user language
            $user->setLanguage($language);
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();
            
            // Update the session with the new locale
            // This is the most reliable way to persist locale across requests
            $request->getSession()->set('_locale', $language);
            
            // Also refresh the user in the security token just in case
            $entityManager->refresh($user);
            
            // Update the token with the refreshed user
            $token = $tokenStorage->getToken();
            if ($token) {
                $token->setUser($user);
                $tokenStorage->setToken($token);
            }

            $this->addFlash('success', 'flash.success.language_updated');

            return $this->redirectToRoute('admin_profile');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'flash.error.language_update_failed');
            return $this->redirectToRoute('admin_profile');
        }
    }

    #[Route('/admin/profile/update-theme', name: 'admin_user_update_theme', methods: ['POST'])]
    public function updateTheme(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException();
        }

        // Verify CSRF token
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('update_theme', $submittedToken)) {
            $this->addFlash('error', 'flash.error.invalid_csrf');
            return $this->redirectToRoute('admin_profile');
        }

        try {
            $theme = $request->request->get('theme');
            
            // Validate theme
            $allowedThemes = ['light', 'dark', 'system'];
            if (!in_array($theme, $allowedThemes)) {
                throw new \InvalidArgumentException('Invalid theme selected.');
            }

            // Update user theme
            $user->setTheme($theme);
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'flash.success.theme_updated');

            return $this->redirectToRoute('admin_profile');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'flash.error.theme_update_failed');
            return $this->redirectToRoute('admin_profile');
        }
    }

    #[Route('/admin/profile/update-password', name: 'admin_user_update_password', methods: ['POST'])]
    public function updatePassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();
        
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException();
        }

        // Verify CSRF token
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('update_password', $submittedToken)) {
            $this->addFlash('error', 'flash.error.invalid_csrf');
            return $this->redirectToRoute('admin_profile');
        }

        try {
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');
            
            // Validate that all fields are provided
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $this->addFlash('error', 'flash.error.password_fields_required');
                return $this->redirectToRoute('admin_profile');
            }
            
            // Verify current password
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'flash.error.current_password_invalid');
                return $this->redirectToRoute('admin_profile');
            }
            
            // Validate new password length
            if (strlen($newPassword) < 10) {
                $this->addFlash('error', 'flash.error.password_too_short');
                return $this->redirectToRoute('admin_profile');
            }
            
            // Validate that new passwords match
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'flash.error.passwords_dont_match');
                return $this->redirectToRoute('admin_profile');
            }
            
            // Validate that new password is different from current
            if ($passwordHasher->isPasswordValid($user, $newPassword)) {
                $this->addFlash('error', 'flash.error.password_same_as_current');
                return $this->redirectToRoute('admin_profile');
            }

            // Hash and update password
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'flash.success.password_updated');

            return $this->redirectToRoute('admin_profile');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'flash.error.password_update_failed');
            return $this->redirectToRoute('admin_profile');
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

    #[Route('/admin/profile/revoke-session/{id}', name: 'admin_user_revoke_session', methods: ['POST'])]
    public function revokeSession(
        UserSession $userSession,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $user = $this->getUser();
        
        // Security check: ensure the session belongs to the current user
        if ($userSession->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        // Verify CSRF token
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('revoke_session_' . $userSession->getId(), $submittedToken)) {
            $this->addFlash('error', 'flash.error.invalid_csrf');
            return $this->redirectToRoute('admin_profile');
        }

        try {
            $userSession->setIsRevoked(true);
            $entityManager->flush();

            $this->addFlash('success', 'Session révoquée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la révocation de la session.');
        }

        return $this->redirectToRoute('admin_profile');
    }
    #[Route('/admin/profile/update-notifications', name: 'admin_user_update_notifications', methods: ['POST'])]
    public function updateNotifications(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $notificationSetting = $user->getNotificationSetting();

        if (!$notificationSetting) {
            $notificationSetting = new UserNotificationSetting();
            $notificationSetting->setUser($user);
            $entityManager->persist($notificationSetting);
        }

        $form = $this->createForm(NotificationSettingType::class, $notificationSetting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Paramètres de notification mis à jour avec succès.');
        } else {
            $this->addFlash('error', 'Erreur lors de la mise à jour des paramètres.');
        }

        return $this->redirectToRoute('admin_profile');
    }
}