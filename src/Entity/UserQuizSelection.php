<?php
// src/Entity/UserQuizSelection.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserQuizSelectionRepository;

#[ORM\Entity(repositoryClass: UserQuizSelectionRepository::class)]
class UserQuizSelection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $score = null;

    #[ORM\Column(type: 'datetime')]
    private $selectionDate;

    #[ORM\OneToMany(targetEntity: UserQuizResult::class, mappedBy: 'userQuizSelection', cascade: ['persist', 'remove'])]
    private Collection $userQuizResults;

    #[ORM\ManyToOne(inversedBy: 'userQuizSelections')]
    private ?User $user = null;

    /**
     * @var Collection<int, QuizResponse>
     */
    #[ORM\ManyToMany(targetEntity: QuizResponse::class, inversedBy: 'userQuizSelections')]
    private Collection $response;

    public function __construct()
    {
        $this->userQuizResults = new ArrayCollection();
        $this->selectionDate = new \DateTime(); // Initialize selectionDate to current date and time
        $this->response = new ArrayCollection();
    }

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = $score;
        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
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

    public function getSelectionDate(): ?\DateTimeInterface
    {
        return $this->selectionDate;
    }

    public function setSelectionDate(\DateTimeInterface $selectionDate): self
    {
        $this->selectionDate = $selectionDate;
        return $this;
    }

    /**
     * @return Collection<int, UserQuizResult>
     */
    public function getUserQuizResults(): Collection
    {
        return $this->userQuizResults;
    }

    public function addUserQuizResult(UserQuizResult $userQuizResult): self
    {
        if (!$this->userQuizResults->contains($userQuizResult)) {
            $this->userQuizResults[] = $userQuizResult;
            $userQuizResult->setUserQuizSelection($this);
        }
        return $this;
    }

    public function removeUserQuizResult(UserQuizResult $userQuizResult): self
    {
        if ($this->userQuizResults->removeElement($userQuizResult)) {
            if ($userQuizResult->getUserQuizSelection() === $this) {
                $userQuizResult->setUserQuizSelection(null);
            }
        }
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, QuizResponse>
     */
    public function getResponse(): Collection
    {
        return $this->response;
    }

    public function addResponse(QuizResponse $response): static
    {
        if (!$this->response->contains($response)) {
            $this->response->add($response);
        }

        return $this;
    }

    public function removeResponse(QuizResponse $response): static
    {
        $this->response->removeElement($response);

        return $this;
    }
}