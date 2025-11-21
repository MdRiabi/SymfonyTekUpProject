<?php namespace App\Controller\Admin;

use App\Repository\UtilisateurRepository;
use App\Repository\RoleRepository;
use App\Repository\AccountRequestRepository;
use App\Form\Admin\UserCreationType;
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