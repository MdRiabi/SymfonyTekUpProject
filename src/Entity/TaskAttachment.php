<?php

namespace App\Entity;

use App\Repository\TaskAttachmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskAttachmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class TaskAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Tache::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tache $tache = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $uploadedBy = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $filename = null;

    #[ORM\Column(type: Types::STRING, length: 500)]
    private ?string $filepath = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $filesize = 0; // en bytes

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\PrePersist]
    public function setUploadedAtValue(): void
    {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTache(): ?Tache
    {
        return $this->tache;
    }

    public function setTache(?Tache $tache): static
    {
        $this->tache = $tache;
        return $this;
    }

    public function getUploadedBy(): ?Utilisateur
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?Utilisateur $uploadedBy): static
    {
        $this->uploadedBy = $uploadedBy;
        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    public function getFilepath(): ?string
    {
        return $this->filepath;
    }

    public function setFilepath(string $filepath): static
    {
        $this->filepath = $filepath;
        return $this;
    }

    public function getFilesize(): ?int
    {
        return $this->filesize;
    }

    public function setFilesize(int $filesize): static
    {
        $this->filesize = $filesize;
        return $this;
    }

    /**
     * Retourne la taille formatée (ex: "2.5 MB")
     */
    public function getFilesizeFormatted(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->filesize;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }
}
