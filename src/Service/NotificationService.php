<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UtilisateurRepository $userRepository
    ) {}

    /**
     * Notifie tous les administrateurs
     */
    public function notifyAdmins(string $titre, string $message, string $type, ?int $relatedId = null): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        
        foreach ($admins as $admin) {
            $this->createNotification($admin, $titre, $message, $type, $relatedId);
        }
        
        $this->em->flush();
    }
    
    /**
     * Notifie les admins d'un nouveau projet
     */
    public function notifyAdminNewProject(\App\Entity\Projet $projet): void
    {
        $titre = 'Nouveau Projet Soumis';
        $message = sprintf(
            'Le client %s %s a soumis le projet "%s". En attente de validation.',
            $projet->getClient()->getNom(),
            $projet->getClient()->getPrenom(),
            $projet->getNom()
        );
        
        $this->notifyAdmins($titre, $message, 'new_project', $projet->getId());
    }

    /**
     * Notifie le client que son projet a été lancé (GO)
     */
    public function notifyProjectLaunch(\App\Entity\Projet $projet): void
    {
        $client = $projet->getClient();
        $titre = '🎉 Votre projet a été approuvé !';
        $message = sprintf(
            'Bonne nouvelle ! Votre projet "%s" a été approuvé et le développement démarrera prochainement. Budget alloué : %s. Vous recevrez bientôt plus de détails sur le planning.',
            $projet->getNom(),
            $projet->getResourcePlanningData()['budgetEstimate'] ?? 'Non défini'
        );
        
        $this->notifyUser($client, $titre, $message, 'project_approved', $projet->getId());
    }

    /**
     * Notifie le client que son projet a été rejeté (NO-GO)
     */
    public function notifyProjectRejection(\App\Entity\Projet $projet, string $reason): void
    {
        $client = $projet->getClient();
        $titre = '❌ Concernant votre demande de projet';
        $message = sprintf(
            'Après analyse de votre projet "%s", nous ne pouvons malheureusement pas donner suite pour les raisons suivantes : %s. N\'hésitez pas à nous recontacter pour discuter d\'alternatives.',
            $projet->getNom(),
            $reason
        );
        
        $this->notifyUser($client, $titre, $message, 'project_rejected', $projet->getId());
    }

    /**
     * Notifie le client qu'une révision est demandée
     */
    public function notifyProjectRevision(\App\Entity\Projet $projet, string $remarks): void
    {
        $client = $projet->getClient();
        $titre = '🔄 Modifications demandées sur votre projet';
        $message = sprintf(
            'Nous avons examiné votre projet "%s". Avant de pouvoir le valider, nous avons besoin de quelques précisions : %s. Merci de modifier votre demande en conséquence.',
            $projet->getNom(),
            $remarks
        );
        
        $this->notifyUser($client, $titre, $message, 'project_revision_requested', $projet->getId());
    }

    public function notifyUser(Utilisateur $user, string $titre, string $message, string $type, ?int $relatedId = null): void
    {
        $this->createNotification($user, $titre, $message, $type, $relatedId);
        $this->em->flush();
    }
    
    /**
     * Crée une notification
     */
    private function createNotification(Utilisateur $user, string $titre, string $message, string $type, ?int $relatedId): void
    {
        $notification = new Notification();
        $notification->setUtilisateur($user);
        $notification->setTitre($titre);
        $notification->setMessage($message);
        $notification->setType($type);
        $notification->setRelatedId($relatedId);
        $notification->setLu(false);
        $notification->setDateCreation(new \DateTimeImmutable());
        
        $this->em->persist($notification);
    }
    
    /**
     * Marque une notification comme lue
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->setLu(true);
        $this->em->flush();
    }
    
    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead(Utilisateur $user): void
    {
        $notifications = $this->em->getRepository(Notification::class)
            ->findBy(['utilisateur' => $user, 'lu' => false]);
        
        foreach ($notifications as $notification) {
            $notification->setLu(true);
        }
        
        $this->em->flush();
    }

    /**
     * Notify team members and client that project configuration is finalized
     * 
     * @param \App\Entity\Projet $projet
     * @param array $assignedMembers Array of Utilisateur objects
     * @param string $customMessage Optional custom message
     */
    public function notifyConfigurationFinalized(\App\Entity\Projet $projet, array $assignedMembers, string $customMessage = ''): void
    {
        $titre = 'Configuration du Projet Finalisée';
        
        // Base message
        $message = sprintf(
            'La configuration du projet "%s" a été finalisée. Le projet est maintenant actif.',
            $projet->getNom()
        );
        
        // Add custom message if provided
        if (!empty($customMessage)) {
            $message .= "\n\nMessage de l'administrateur :\n" . $customMessage;
        }
        
        // Notify all assigned team members
        foreach ($assignedMembers as $member) {
            $this->createNotification(
                $member,
                $titre,
                $message,
                'project_configured',
                $projet->getId()
            );
        }
        
        // Notify the client
        if ($projet->getClient()) {
            $clientMessage = sprintf(
                'La configuration de votre projet "%s" a été finalisée. L\'équipe va maintenant commencer à travailler sur votre projet.',
                $projet->getNom()
            );
            
            if (!empty($customMessage)) {
                $clientMessage .= "\n\n" . $customMessage;
            }
            
            $this->createNotification(
                $projet->getClient(),
                $titre,
                $clientMessage,
                'project_configured',
                $projet->getId()
            );
        }
        
        $this->em->flush();
    }
}
