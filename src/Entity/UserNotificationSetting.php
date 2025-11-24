<?php

namespace App\Entity;

use App\Repository\UserNotificationSettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserNotificationSettingRepository::class)]
class UserNotificationSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'notificationSetting', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user = null;

    // PARTICIPANT
    #[ORM\Column]
    private ?bool $notifyMentioned = true;

    #[ORM\Column]
    private ?bool $notifyWatcher = true;

    #[ORM\Column]
    private ?bool $notifyAssigned = true;

    #[ORM\Column]
    private ?bool $notifyResponsible = true;

    #[ORM\Column]
    private ?bool $notifyShared = false;

    // ALERTES DE DATE
    #[ORM\Column]
    private ?bool $notifyStartDate = true;

    #[ORM\Column(length: 20)]
    private ?string $startDateDelay = '1_day'; // 1_day, 3_days, 1_week

    #[ORM\Column]
    private ?bool $notifyEndDate = true;

    #[ORM\Column(length: 20)]
    private ?string $endDateDelay = '1_day';

    #[ORM\Column]
    private ?bool $notifyOverdue = true;

    #[ORM\Column(length: 20)]
    private ?string $overdueFrequency = 'daily'; // daily, weekly

    // NON PARTICIPANT
    #[ORM\Column]
    private ?bool $notifyNewWorkPackage = false;

    #[ORM\Column]
    private ?bool $notifyStatusChanges = false;

    #[ORM\Column]
    private ?bool $notifyDateChanges = false;

    #[ORM\Column]
    private ?bool $notifyPriorityChanges = false;

    #[ORM\Column]
    private ?bool $notifyNewComments = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Utilisateur
    {
        return $this->user;
    }

    public function setUser(Utilisateur $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isNotifyMentioned(): ?bool
    {
        return $this->notifyMentioned;
    }

    public function setNotifyMentioned(bool $notifyMentioned): static
    {
        $this->notifyMentioned = $notifyMentioned;

        return $this;
    }

    public function isNotifyWatcher(): ?bool
    {
        return $this->notifyWatcher;
    }

    public function setNotifyWatcher(bool $notifyWatcher): static
    {
        $this->notifyWatcher = $notifyWatcher;

        return $this;
    }

    public function isNotifyAssigned(): ?bool
    {
        return $this->notifyAssigned;
    }

    public function setNotifyAssigned(bool $notifyAssigned): static
    {
        $this->notifyAssigned = $notifyAssigned;

        return $this;
    }

    public function isNotifyResponsible(): ?bool
    {
        return $this->notifyResponsible;
    }

    public function setNotifyResponsible(bool $notifyResponsible): static
    {
        $this->notifyResponsible = $notifyResponsible;

        return $this;
    }

    public function isNotifyShared(): ?bool
    {
        return $this->notifyShared;
    }

    public function setNotifyShared(bool $notifyShared): static
    {
        $this->notifyShared = $notifyShared;

        return $this;
    }

    public function isNotifyStartDate(): ?bool
    {
        return $this->notifyStartDate;
    }

    public function setNotifyStartDate(bool $notifyStartDate): static
    {
        $this->notifyStartDate = $notifyStartDate;

        return $this;
    }

    public function getStartDateDelay(): ?string
    {
        return $this->startDateDelay;
    }

    public function setStartDateDelay(string $startDateDelay): static
    {
        $this->startDateDelay = $startDateDelay;

        return $this;
    }

    public function isNotifyEndDate(): ?bool
    {
        return $this->notifyEndDate;
    }

    public function setNotifyEndDate(bool $notifyEndDate): static
    {
        $this->notifyEndDate = $notifyEndDate;

        return $this;
    }

    public function getEndDateDelay(): ?string
    {
        return $this->endDateDelay;
    }

    public function setEndDateDelay(string $endDateDelay): static
    {
        $this->endDateDelay = $endDateDelay;

        return $this;
    }

    public function isNotifyOverdue(): ?bool
    {
        return $this->notifyOverdue;
    }

    public function setNotifyOverdue(bool $notifyOverdue): static
    {
        $this->notifyOverdue = $notifyOverdue;

        return $this;
    }

    public function getOverdueFrequency(): ?string
    {
        return $this->overdueFrequency;
    }

    public function setOverdueFrequency(string $overdueFrequency): static
    {
        $this->overdueFrequency = $overdueFrequency;

        return $this;
    }

    public function isNotifyNewWorkPackage(): ?bool
    {
        return $this->notifyNewWorkPackage;
    }

    public function setNotifyNewWorkPackage(bool $notifyNewWorkPackage): static
    {
        $this->notifyNewWorkPackage = $notifyNewWorkPackage;

        return $this;
    }

    public function isNotifyStatusChanges(): ?bool
    {
        return $this->notifyStatusChanges;
    }

    public function setNotifyStatusChanges(bool $notifyStatusChanges): static
    {
        $this->notifyStatusChanges = $notifyStatusChanges;

        return $this;
    }

    public function isNotifyDateChanges(): ?bool
    {
        return $this->notifyDateChanges;
    }

    public function setNotifyDateChanges(bool $notifyDateChanges): static
    {
        $this->notifyDateChanges = $notifyDateChanges;

        return $this;
    }

    public function isNotifyPriorityChanges(): ?bool
    {
        return $this->notifyPriorityChanges;
    }

    public function setNotifyPriorityChanges(bool $notifyPriorityChanges): static
    {
        $this->notifyPriorityChanges = $notifyPriorityChanges;

        return $this;
    }

    public function isNotifyNewComments(): ?bool
    {
        return $this->notifyNewComments;
    }

    public function setNotifyNewComments(bool $notifyNewComments): static
    {
        $this->notifyNewComments = $notifyNewComments;

        return $this;
    }
}
