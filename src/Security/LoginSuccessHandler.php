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
        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        if (in_array('ROLE_CLIENT', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('client_dashboard'));
        }

        if (in_array('ROLE_PROJECT_MANAGER', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('project_manager_dashboard'));
        }

        if (in_array('ROLE_TEAM_MEMBER', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('team_member_dashboard'));
        }

        // Par défaut, rediriger vers le dashboard client
        return new RedirectResponse($this->urlGenerator->generate('client_dashboard'));
    }
}
