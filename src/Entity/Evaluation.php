<?php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Validation ajoutée ici : title ne peut pas être vide
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 4,
        minMessage: "La description doit contenir au moins {{ limit }} caractères"
    )]
    private ?string $description = null;

    // Validation : max_score doit être calculé automatiquement
    #[ORM\Column]
    private ?int $max_score = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $create_at = null;

    // Validation pour la date limite : doit être supérieure à la date actuelle (déjà existante)
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date limite est obligatoire")]
    #[Assert\GreaterThan("today", message: "La date limite doit être supérieure à la date actuelle")]
    private ?\DateTimeInterface $deadline = null;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'evaluation', orphanRemoval: true)]
    private Collection $questions;

    #[ORM\OneToMany(mappedBy: 'evaluation', targetEntity: Reponse::class, orphanRemoval: true)]
    private Collection $reponses;

    #[ORM\ManyToOne(targetEntity: Cours::class, inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Cours $cours = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: 0,
        max: 20,
        notInRangeMessage: 'Le score minimum doit être entre {{ min }} et {{ max }}',
    )]
    private ?int $badgeThreshold = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le titre du badge doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le titre du badge ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\When(
        expression: 'this.getBadgeThreshold() !== null',
        constraints: [
            new Assert\NotBlank(message: 'Le titre du badge est obligatoire si un seuil est défini')
        ]
    )]
    private ?string $badgeTitle = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\When(
        expression: 'this.getBadgeThreshold() !== null',
        constraints: [
            new Assert\NotBlank(message: 'L\'icône du badge est obligatoire si un seuil est défini')
        ]
    )]
    private ?string $badgeImage = null;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->reponses = new ArrayCollection();
        $this->create_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
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

    public function getMaxScore(): ?int
    {
        // Calculer le max_score comme la somme des points des questions
        $total = 0;
        foreach ($this->questions as $question) {
            $total += $question->getPoint();
        }
        return $total;
    }

    public function setMaxScore(?int $max_score): static
    {
        // Ne rien faire car le max_score est calculé automatiquement
        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeInterface $create_at): static
    {
        $this->create_at = $create_at;
        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): static
    {
        $this->deadline = $deadline;
        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setEvaluation($this);
        }
        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getEvaluation() === $this) {
                $question->setEvaluation(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setEvaluation($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getEvaluation() === $this) {
                $reponse->setEvaluation(null);
            }
        }

        return $this;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): static
    {
        $this->cours = $cours;

        return $this;
    }

    public function getBadgeThreshold(): ?int
    {
        return $this->badgeThreshold;
    }

    public function setBadgeThreshold(?int $badgeThreshold): self
    {
        $this->badgeThreshold = $badgeThreshold;
        return $this;
    }

    public function getBadgeImage(): ?string
    {
        return $this->badgeImage;
    }

    public function setBadgeImage(?string $badgeImage): static
    {
        $this->badgeImage = $badgeImage;
        return $this;
    }

    public function getBadgeTitle(): ?string
    {
        return $this->badgeTitle;
    }

    public function setBadgeTitle(?string $badgeTitle): self
    {
        $this->badgeTitle = $badgeTitle;
        return $this;
    }
}