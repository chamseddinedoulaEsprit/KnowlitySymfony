<?php
// src/Entity/UserQuizResult.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_quiz_result')]
class UserQuizResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $score = 0;

    #[ORM\Column(type: 'datetime')]
    private $soumisLe;

    #[ORM\Column(type: 'text', nullable: true)]
    private $response;

    #[ORM\Column(type: 'text', nullable: true)]
    private $feedback;

    #[ORM\ManyToOne(targetEntity: Quiz::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $quiz;

    #[ORM\ManyToOne(targetEntity: QuizQuestion::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $quizQuestion;

    #[ORM\ManyToOne(targetEntity: UserQuizSelection::class, inversedBy: 'userQuizResults')]
    #[ORM\JoinColumn(nullable: false)]
    private $userQuizSelection;

    public function __construct()
    {
        $this->soumisLe = new \DateTime();
    }

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;
        return $this;
    }

    public function getSoumisLe(): ?\DateTimeInterface
    {
        return $this->soumisLe;
    }

    public function setSoumisLe(\DateTimeInterface $soumisLe): self
    {
        $this->soumisLe = $soumisLe;
        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): self
    {
        $this->response = $response;
        return $this;
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setFeedback(?string $feedback): self
    {
        $this->feedback = $feedback;
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

    public function getQuizQuestion(): ?QuizQuestion
    {
        return $this->quizQuestion;
    }

    public function setQuizQuestion(?QuizQuestion $quizQuestion): self
    {
        $this->quizQuestion = $quizQuestion;
        return $this;
    }

    public function getUserQuizSelection(): ?UserQuizSelection
    {
        return $this->userQuizSelection;
    }

    public function setUserQuizSelection(?UserQuizSelection $userQuizSelection): self
    {
        $this->userQuizSelection = $userQuizSelection;
        return $this;
    }
}