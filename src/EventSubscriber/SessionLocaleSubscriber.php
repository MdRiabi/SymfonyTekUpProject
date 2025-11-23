<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

class SessionLocaleSubscriber implements EventSubscriberInterface
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // Check if session exists and has locale
        if ($request->hasPreviousSession()) {
            $sessionLocale = $request->getSession()->get('_locale');
            
            if ($sessionLocale) {
                $request->setLocale($sessionLocale);
                
                if ($this->logger) {
                    $this->logger->info('SessionLocaleSubscriber: Locale set from session', [
                        'locale' => $sessionLocale
                    ]);
                }
            } else {
                if ($this->logger) {
                    $this->logger->info('SessionLocaleSubscriber: No locale in session');
                }
            }
        } else {
            if ($this->logger) {
                $this->logger->info('SessionLocaleSubscriber: No session found');
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Priority 20: Run BEFORE LocaleListener (16) and LocaleAwareListener (15)
            // This ensures the locale is set early enough for all services
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
