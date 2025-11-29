<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
#[ORM\Table(name: 'projet')]
class Projet
{
    // Status constants
    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_EN_CONFIGURATION = 'EN_CONFIGURATION';
    public const STATUT_CONFIGURE = 'CONFIGURE';
    public const STATUT_ACTIF = 'ACTIF';
    public const STATUT_TERMINE = 'TERMINE';
    public const STATUT_REJETE = 'REJETE';
    public const STATUT_ARCHIVE = 'ARCHIVE';

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

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    private ?Utilisateur $responsable = null;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: Tache::class)]
    private Collection $taches;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(length: 50)]
    private ?string $categorie = null;

    #[ORM\Column(type: 'text')]
    private ?string $objectifs = null;

    #[ORM\Column(type: 'json')]
    private array $fonctionnalites = [];

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $budget = null;

    #[ORM\Column(length: 20)]
    private string $priorite = 'medium';

    #[ORM\Column(length: 20)]
    private string $statut = 'EN_ATTENTE';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $client = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateModification = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateApprobation = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    private ?Utilisateur $approuvePar = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $raisonRefus = null;

    #[ORM\Column(nullable: true)]
    private ?int $charterStep = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $businessCaseData = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $scopeDefinitionData = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $resourcePlanningData = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $goNoGoData = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $revisionRequests = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $revisionCount = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierJoint = null;

    #[ORM\OneToMany(mappedBy: 'projet', targetEntity: Phase::class, orphanRemoval: true)]
    private Collection $phases;

    public function __construct()
    {
        $this->taches = new ArrayCollection();
        $this->phases = new ArrayCollection();
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

    public function getResponsable(): ?Utilisateur
    {
        return $this->responsable;
    }

    public function setResponsable(?Utilisateur $responsable): self
    {
        $this->responsable = $responsable;
        return $this;
    }

    public function getTaches(): Collection
    {
        return $this->taches;
    }

    public function addTache(Tache $tache): self
    {
        if (!$this->taches->contains($tache)) {
            $this->taches->add($tache);
            $tache->setProjet($this);
        }
        return $this;
    }

    public function removeTache(Tache $tache): self
    {
        if ($this->taches->removeElement($tache)) {
            if ($tache->getProjet() === $this) {
                $tache->setProjet(null);
            }
        }
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getObjectifs(): ?string
    {
        return $this->objectifs;
    }

    public function setObjectifs(string $objectifs): self
    {
        $this->objectifs = $objectifs;
        return $this;
    }

    public function getFonctionnalites(): array
    {
        return $this->fonctionnalites;
    }

    public function setFonctionnalites(array $fonctionnalites): self
    {
        $this->fonctionnalites = $fonctionnalites;
        return $this;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(?string $budget): self
    {
        $this->budget = $budget;
        return $this;
    }

    public function getPriorite(): string
    {
        return $this->priorite;
    }

    public function setPriorite(string $priorite): self
    {
        $this->priorite = $priorite;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getClient(): ?Utilisateur
    {
        return $this->client;
    }

    public function setClient(?Utilisateur $client): self
    {
        $this->client = $client;
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

    public function getDateModification(): ?\DateTimeImmutable
    {
        return $this->dateModification;
    }

    public function setDateModification(\DateTimeImmutable $dateModification): self
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    public function getDateApprobation(): ?\DateTimeImmutable
    {
        return $this->dateApprobation;
    }

    public function setDateApprobation(?\DateTimeImmutable $dateApprobation): self
    {
        $this->dateApprobation = $dateApprobation;
        return $this;
    }

    public function getApprouvePar(): ?Utilisateur
    {
        return $this->approuvePar;
    }

    public function setApprouvePar(?Utilisateur $approuvePar): self
    {
        $this->approuvePar = $approuvePar;
        return $this;
    }

    public function getRaisonRefus(): ?string
    {
        return $this->raisonRefus;
    }

    public function setRaisonRefus(?string $raisonRefus): self
    {
        $this->raisonRefus = $raisonRefus;
        return $this;
    }

    public function getFichierJoint(): ?string
    {
        return $this->fichierJoint;
    }

    public function setFichierJoint(?string $fichierJoint): self
    {
        $this->fichierJoint = $fichierJoint;
        return $this;
    }

    public function getCharterStep(): ?int
    {
        return $this->charterStep;
    }

    public function setCharterStep(?int $charterStep): self
    {
        $this->charterStep = $charterStep;
        return $this;
    }

    public function getBusinessCaseData(): ?array
    {
        return $this->businessCaseData;
    }

    public function setBusinessCaseData(?array $businessCaseData): self
    {
        $this->businessCaseData = $businessCaseData;
        return $this;
    }

    public function getScopeDefinitionData(): ?array
    {
        return $this->scopeDefinitionData;
    }

    public function setScopeDefinitionData(?array $scopeDefinitionData): self
    {
        $this->scopeDefinitionData = $scopeDefinitionData;
        return $this;
    }

    public function getResourcePlanningData(): ?array
    {
        return $this->resourcePlanningData;
    }

    public function setResourcePlanningData(?array $resourcePlanningData): self
    {
        $this->resourcePlanningData = $resourcePlanningData;
        return $this;
    }

    public function getGoNoGoData(): ?array
    {
        return $this->goNoGoData;
    }

    public function setGoNoGoData(?array $goNoGoData): self
    {
        $this->goNoGoData = $goNoGoData;
        return $this;
    }

    public function getRevisionRequests(): ?array
    {
        return $this->revisionRequests;
    }

    public function setRevisionRequests(?array $revisionRequests): self
    {
        $this->revisionRequests = $revisionRequests;
        return $this;
    }

    public function getRevisionCount(): int
    {
        return $this->revisionCount;
    }

    public function setRevisionCount(int $revisionCount): self
    {
        $this->revisionCount = $revisionCount;
        return $this;
    }

    /**
     * @return Collection<int, Phase>
     */
    public function getPhases(): Collection
    {
        return $this->phases;
    }

    public function addPhase(Phase $phase): self
    {
        if (!$this->phases->contains($phase)) {
            $this->phases->add($phase);
            $phase->setProjet($this);
        }

        return $this;
    }

    public function removePhase(Phase $phase): self
    {
        if ($this->phases->removeElement($phase)) {
            // set the owning side to null (unless already changed)
            if ($phase->getProjet() === $this) {
                $phase->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * Check if the project is ready to be finalized
     * 
     * @return array ['ready' => bool, 'errors' => array, 'warnings' => array]
     */
    public function isReadyToFinalize(): array
    {
        $errors = [];
        $warnings = [];

        // Check if at least one phase exists
        if ($this->phases->isEmpty()) {
            $errors[] = 'Veuillez créer au moins une phase avant de finaliser la configuration.';
        }

        // Check if at least one task exists
        if ($this->taches->isEmpty()) {
            $errors[] = 'Veuillez créer au moins une tâche avant de finaliser la configuration.';
        }

        // Check if all tasks are assigned (warning only)
        $unassignedCount = 0;
        $tasksWithoutDeadline = 0;
        
        foreach ($this->taches as $tache) {
            if (!$tache->getAssigne()) {
                $unassignedCount++;
            }
            if (!$tache->getDeadline()) {
                $tasksWithoutDeadline++;
            }
        }

        if ($unassignedCount > 0) {
            $warnings[] = sprintf('%d tâche(s) ne sont pas encore assignées.', $unassignedCount);
        }

        if ($tasksWithoutDeadline > 0) {
            $warnings[] = sprintf('%d tâche(s) n\'ont pas de deadline définie.', $tasksWithoutDeadline);
        }

        return [
            'ready' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'stats' => [
                'phases' => $this->phases->count(),
                'tasks' => $this->taches->count(),
                'assigned' => $this->taches->count() - $unassignedCount,
                'unassigned' => $unassignedCount,
            ]
        ];
    }
}
