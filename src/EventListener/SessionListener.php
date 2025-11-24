<?php

namespace App\EventListener;

use App\Entity\UserSession;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SessionListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $this->requestStack->getSession();
        
        if (!$session->isStarted()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser() instanceof Utilisateur) {
            return;
        }

        $user = $token->getUser();
        $sessionId = $session->getId();

        // Try to find existing session
        $userSession = $this->entityManager->getRepository(UserSession::class)->findOneBy([
            'sessionId' => $sessionId
        ]);

        if ($userSession) {
            // Check if session is revoked
            if ($userSession->isRevoked()) {
                $session->invalidate();
                $this->tokenStorage->setToken(null);
                
                // Redirect to homepage
                $response = new RedirectResponse($this->urlGenerator->generate('app_home'));
                $event->setResponse($response);
                return;
            }

            // Update existing session
            $userSession->setLastActiveAt(new \DateTimeImmutable());
            // Update IP if changed
            if ($userSession->getIpAddress() !== $request->getClientIp()) {
                $userSession->setIpAddress($request->getClientIp() ?? 'unknown');
            }
        } else {
            // Create new session if not found (e.g. session started but not yet recorded)
            $userSession = new UserSession();
            $userSession->setUser($user);
            $userSession->setSessionId($sessionId);
            $userSession->setIpAddress($request->getClientIp() ?? 'unknown');
            $userSession->setUserAgent($request->headers->get('User-Agent'));
            $this->entityManager->persist($userSession);
        }

        $this->entityManager->flush();
    }

    #[AsEventListener(event: SecurityEvents::INTERACTIVE_LOGIN)]
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        
        if (!$user instanceof Utilisateur) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $sessionId = $session->getId();

        // Check if session already exists (cleanup old sessions with same ID if any)
        $existingSession = $this->entityManager->getRepository(UserSession::class)->findOneBy([
            'sessionId' => $sessionId
        ]);

        if ($existingSession) {
            $existingSession->setLastActiveAt(new \DateTimeImmutable());
        } else {
            $userSession = new UserSession();
            $userSession->setUser($user);
            $userSession->setSessionId($sessionId);
            $userSession->setIpAddress($request->getClientIp() ?? 'unknown');
            $userSession->setUserAgent($request->headers->get('User-Agent'));
            
            $this->entityManager->persist($userSession);
        }

        $this->entityManager->flush();
    }
}
