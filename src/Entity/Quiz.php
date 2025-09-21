<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[Assert\Length(
        max: 500,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Assert\NotBlank(message: "Le score maximum est obligatoire.")]
    #[Assert\Positive(message: "Le score maximum doit être un nombre positif.")]
    #[ORM\Column(type: 'integer')]
    private ?int $scoreMax = null;

    #[Assert\NotBlank(message: "La date limite est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class, message: "La date limite doit être une date valide.")]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateLimite = null;

    #[ORM\OneToMany(targetEntity: QuizQuestion::class, mappedBy: 'quiz', cascade: ['persist', 'remove'])]
    private Collection $questions;

    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    private ?Cours $Cours = null;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getScoreMax(): ?int
    {
        return $this->scoreMax;
    }

    public function setScoreMax(int $scoreMax): static
    {
        $this->scoreMax = $scoreMax;
        return $this;
    }

    public function getDateLimite(): ?\DateTimeInterface
    {
        return $this->dateLimite;
    }

    public function setDateLimite(\DateTimeInterface $dateLimite): static
    {
        $this->dateLimite = $dateLimite;
        return $this;
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(QuizQuestion $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setQuiz($this);
        }
        return $this;
    }

    public function removeQuestion(QuizQuestion $question): static
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
            }
        }
        return $this;
    }

    public function getCour(): ?Cours
    {
        return $this->Cours;
    }

    public function setCour(?Cours $Cour): static
    {
        $this->Cours = $Cour;

        return $this;
    }
}
