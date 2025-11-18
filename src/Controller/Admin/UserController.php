<?php

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/users', name: 'admin_manage_users')]
    public function list(UtilisateurRepository $utilisateurRepository): Response
    {
        $users = $utilisateurRepository->findAll();

        return $this->render('admin/users/list.html.twig', [
            'users' => $users,
        ]);
    }



    #[Route('/users', name: 'admin_manage_users')]
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