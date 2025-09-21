<?php
namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $urlImage = null;

    /**
     * @var Collection<int, Chapitre>
     */
    #[ORM\OneToMany(mappedBy: 'cours', targetEntity: Chapitre::class, cascade: ['remove'], orphanRemoval: true)]    
    private Collection $chapitres;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[Assert\NotNull(message: "La matière ne peut pas être vide.")]
    private ?Matiere $matiere = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La langue est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "La langue doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La langue ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Choice(
        choices: ['fr', 'en', 'es', 'de', 'ar'],
        message: "Langue non valide. Choix possibles : fr, en, es, de, ar."
    )]
    private ?string $langue = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    #[Assert\Type(
        type: 'integer',
        message: "Le prix doit être un nombre entier."
    )]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif.")]
    private ?int $prix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lienDePaiment = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'enseignant doit être spécifié.")]
    private ?User $enseignant = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'cours')]
    private Collection $etudiants;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'cours')]   
    #[ORM\JoinTable(name: 'cours_etudiants_favoris')]
    private Collection $etudiantsfavoris;

    /**
     * @var Collection<int, Evaluation>
     */
    #[ORM\OneToMany(mappedBy: 'cours', targetEntity: Evaluation::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $evaluations;

    #[Assert\Callback]
    public function validateLienDePaiment(ExecutionContextInterface $context): void
    {
        if ($this->prix !== 0 && empty($this->lienDePaiment)) {
            $context->buildViolation('Le lien de paiement est obligatoire lorsque le prix est différent de zéro.')
                ->atPath('lienDePaiment')
                ->addViolation();
        }
    }
   /**
     * @var Collection<int, Quiz>
     */
    #[ORM\OneToMany(targetEntity: Quiz::class, mappedBy: 'Cours')]
    private Collection $quizzes;
    public function __construct()
    {
        $this->quizzes = new ArrayCollection();
        $this->chapitres = new ArrayCollection();
        $this->etudiants = new ArrayCollection();
        $this->etudiantsfavoris = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
        
    }
    public function isUserFavorite(int $userId): bool
{
    foreach ($this->etudiantsfavoris as $etudiant) {
        if ($etudiant->getId() === $userId) {
            return true;
        }
    }

    return false;
}


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function getDureeTotale(): int {
        return array_reduce(
            $this->chapitres->toArray(), 
            fn(int $total, Chapitre $chapitre) => $total + $chapitre->getDureeEstimee(), 
            0
        );
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUrlImage(): ?string
    {
        return $this->urlImage;
    }

    public function setUrlImage(string $urlImage): static
    {
        $this->urlImage = $urlImage;

        return $this;
    }

    /**
     * @return Collection<int, Chapitre>
     */
    public function getChapitres(): Collection
    {
        return $this->chapitres;
    }
    

    public function addChapitre(Chapitre $chapitre): static
    {
        if (!$this->chapitres->contains($chapitre)) {
            $this->chapitres->add($chapitre);
            $chapitre->setCours($this);
        }

        return $this;
    }

    public function removeChapitre(Chapitre $chapitre): static
    {
        if ($this->chapitres->removeElement($chapitre)) {
            if ($chapitre->getCours() === $this) {
                $chapitre->setCours(null);
            }
        }

        return $this;
    }

    public function getNbChapitres(): int
    {
        return $this->chapitres->count();
    }

    public function getNbEtudiants(): int
    {
        return $this->etudiants->count();
    }

    public function getMatiere(): ?Matiere
    {
        return $this->matiere;
    }

    public function setMatiere(?Matiere $matiere): static
    {
        $this->matiere = $matiere;

        return $this;
    }

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(string $langue): static
    {
        $this->langue = $langue;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getEnseignant(): ?User
    {
        return $this->enseignant;
    }

    public function setEnseignant(?User $enseignant): static
    {
        $this->enseignant = $enseignant;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getEtudiants(): Collection
    {
        return $this->etudiants;
    }

    public function isUserEnrolled(int $id): bool
    {
        foreach ($this->etudiants as $etudiant) {
            if ($etudiant->getId() === $id) {
                return true;
            }
        }
        return false;
    }

    public function addEtudiant(User $etudiant): static
    {
        if (!$this->etudiants->contains($etudiant)) {
            $this->etudiants->add($etudiant);
        }

        return $this;
    }

    public function removeEtudiant(User $etudiant): static
    {
        $this->etudiants->removeElement($etudiant);

        return $this;
    }

     /**
     * @return Collection<int, User>
     */
    public function getEtudiantsfavoris(): Collection
    {
        return $this->etudiants;
    }

    public function isUserEnrolled1(int $id): bool
    {
        foreach ($this->etudiantsfavoris as $etudiant) {
            if ($etudiant->getId() === $id) {
                return true;
            }
        }
        return false;
    }

    public function addEtudiantfavoris(User $etudiant): static
    {
        if (!$this->etudiantsfavoris->contains($etudiant)) {
            $this->etudiantsfavoris->add($etudiant);
        }

        return $this;
    }

    public function removeEtudiantFavoris(User $etudiant): static
    {
        $this->etudiantsfavoris->removeElement($etudiant);

        return $this;
    }

    /**
     * @return Collection<int, Evaluation>
     */
    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    public function addEvaluation(Evaluation $evaluation): static
    {
        if (!$this->evaluations->contains($evaluation)) {
            $this->evaluations->add($evaluation);
            $evaluation->setCours($this);
        }

        return $this;
    }

    public function removeEvaluation(Evaluation $evaluation): static
    {
        if ($this->evaluations->removeElement($evaluation)) {
            if ($evaluation->getCours() === $this) {
                $evaluation->setCours(null);
            }
        }

        return $this;
    }

    public function getLienDePaiment(): ?string
    {
        return $this->lienDePaiment;
    }

    public function setLienDePaiment(string $lienDePaiment): static
    {
        $this->lienDePaiment = $lienDePaiment;

        return $this;
    }
/**
     * @return Collection<int, Quiz>
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setCour($this);
        }

        return $this;
    }

    public function removeQuiz(Quiz $quiz): static
    {
        if ($this->quizzes->removeElement($quiz)) {
            // set the owning side to null (unless already changed)
            if ($quiz->getCour() === $this) {
                $quiz->setCour(null);
            }
        }

        return $this;
    }
   
}

