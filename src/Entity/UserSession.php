<?php

namespace App\Entity;

use App\Repository\UserSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSessionRepository::class)]
class UserSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user = null;

    #[ORM\Column(length: 255)]
    private ?string $sessionId = null;

    #[ORM\Column(length: 45)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastActiveAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isRevoked = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->lastActiveAt = new \DateTimeImmutable();
        $this->isRevoked = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Utilisateur
    {
        return $this->user;
    }

    public function setUser(?Utilisateur $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): static
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getLastActiveAt(): ?\DateTimeImmutable
    {
        return $this->lastActiveAt;
    }

    public function setLastActiveAt(\DateTimeImmutable $lastActiveAt): static
    {
        $this->lastActiveAt = $lastActiveAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isRevoked(): ?bool
    {
        return $this->isRevoked;
    }

    public function setIsRevoked(bool $isRevoked): static
    {
        $this->isRevoked = $isRevoked;

        return $this;
    }

    public function getBrowser(): string
    {
        $agent = $this->userAgent;
        if (empty($agent)) return 'Inconnu';

        if (preg_match('/MSIE/i', $agent) && !preg_match('/Opera/i', $agent)) return 'Internet Explorer';
        if (preg_match('/Firefox/i', $agent)) return 'Firefox';
        if (preg_match('/Edg/i', $agent)) return 'Edge';
        if (preg_match('/Chrome/i', $agent)) return 'Chrome';
        if (preg_match('/Safari/i', $agent)) return 'Safari';
        if (preg_match('/Opera/i', $agent)) return 'Opera';
        if (preg_match('/Netscape/i', $agent)) return 'Netscape';

        return 'Autre';
    }

    public function getPlatform(): string
    {
        $agent = $this->userAgent;
        if (empty($agent)) return 'Inconnu';

        if (preg_match('/linux/i', $agent)) return 'Linux';
        if (preg_match('/macintosh|mac os x/i', $agent)) return 'macOS';
        if (preg_match('/windows|win32/i', $agent)) return 'Windows';
        if (preg_match('/android/i', $agent)) return 'Android';
        if (preg_match('/iphone/i', $agent)) return 'iPhone';
        if (preg_match('/ipad/i', $agent)) return 'iPad';

        return 'Autre';
    }
}
