<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[ORM\Table(name: 'quiz_question')]
class QuizQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    private string $type;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private int $points;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $texte;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\LessThan(20)]
    private int $ordre;

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    #[ORM\OneToMany(targetEntity: QuizResponse::class, mappedBy: 'question', cascade: ['persist', 'remove'])]
    private Collection $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): self
    {
        $this->texte = $texte;
        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;
        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;
        return $this;
    }

    /**
     * @return Collection<int, QuizResponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(QuizResponse $reponse): self
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses[] = $reponse;
            $reponse->setQuestion($this);
        }
        return $this;
    }

    public function removeReponse(QuizResponse $reponse): self
    {
        if ($this->reponses->removeElement($reponse)) {
            if ($reponse->getQuestion() === $this) {
                $reponse->setQuestion(null);
            }
        }
        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validateOrder(ExecutionContextInterface $context): void
    {
        if ($this->quiz) {
            foreach ($this->quiz->getQuestions() as $question) {
                if ($question !== $this && $question->getOrdre() === $this->ordre) {
                    $context->buildViolation('Each question in a quiz must have a unique order.')
                        ->atPath('ordre')
                        ->addViolation();
                    break;
                }
            }
        }
    }
}
