<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        $roles = $user->getRoles();

        // Rediriger selon le rôle de l'utilisateur
        // Admin
        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        // Client
        if (in_array('ROLE_CLIENT', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('client_dashboard'));
        }

        // Chef de Projet
        if (in_array('ROLE_CHEF_DE_PROJET', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        // User (Membre d'équipe)
        if (in_array('ROLE_USER', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('team_dashboard'));
        }

        // Par défaut, rediriger vers le dashboard équipe
        return new RedirectResponse($this->urlGenerator->generate('team_dashboard'));
    }
}
