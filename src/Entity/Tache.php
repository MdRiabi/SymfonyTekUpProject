<?php

namespace App\Entity;

use App\Repository\TacheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
#[ORM\Table(name: 'tache')]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $statut = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'tachesCrees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $createur = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    private ?Utilisateur $assigne = null;

    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'taches')]
    private ?Projet $projet = null;

    #[ORM\ManyToOne(inversedBy: 'taches')]
    private ?Phase $phase = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $priorite = 'MEDIUM';

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deadline = null;

    #[ORM\Column(nullable: true)]
    private ?int $estimatedHours = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $progress = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'tache', targetEntity: TaskNote::class, orphanRemoval: true)]
    private Collection $notes;

    #[ORM\OneToMany(mappedBy: 'tache', targetEntity: TaskAttachment::class, orphanRemoval: true)]
    private Collection $attachments;

    #[ORM\OneToMany(mappedBy: 'tache', targetEntity: TaskComment::class, orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->notes = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeImmutable $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getCreateur(): ?Utilisateur
    {
        return $this->createur;
    }

    public function setCreateur(?Utilisateur $createur): self
    {
        $this->createur = $createur;
        return $this;
    }

    public function getAssigne(): ?Utilisateur
    {
        return $this->assigne;
    }

    public function setAssigne(?Utilisateur $assigne): self
    {
        $this->assigne = $assigne;
        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;
        return $this;
    }

    public function getPhase(): ?Phase
    {
        return $this->phase;
    }

    public function setPhase(?Phase $phase): self
    {
        $this->phase = $phase;
        return $this;
    }

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(?string $priorite): self
    {
        $this->priorite = $priorite;
        return $this;
    }

    public function getDeadline(): ?\DateTimeImmutable
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeImmutable $deadline): self
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function getEstimatedHours(): ?int
    {
        return $this->estimatedHours;
    }

    public function setEstimatedHours(?int $estimatedHours): self
    {
        $this->estimatedHours = $estimatedHours;
        return $this;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): self
    {
        $this->progress = $progress;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    /**
     * @return Collection<int, TaskNote>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(TaskNote $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setTache($this);
        }
        return $this;
    }

    public function removeNote(TaskNote $note): self
    {
        if ($this->notes->removeElement($note)) {
            if ($note->getTache() === $this) {
                $note->setTache(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, TaskAttachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(TaskAttachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setTache($this);
        }
        return $this;
    }

    public function removeAttachment(TaskAttachment $attachment): self
    {
        if ($this->attachments->removeElement($attachment)) {
            if ($attachment->getTache() === $this) {
                $attachment->setTache(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, TaskComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(TaskComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTache($this);
        }
        return $this;
    }

    public function removeComment(TaskComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getTache() === $this) {
                $comment->setTache(null);
            }
        }
        return $this;
    }
}
