<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'quiz_reponse')]
class QuizResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $texte;

    #[ORM\Column(type: 'boolean')]
    private bool $estCorrecte = false;

    #[ORM\ManyToOne(targetEntity: QuizQuestion::class, inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuizQuestion $question = null;

    /**
     * @var Collection<int, UserQuizSelection>
     */
    #[ORM\ManyToMany(targetEntity: UserQuizSelection::class, mappedBy: 'response')]
    private Collection $userQuizSelections;

    public function __construct()
    {
        $this->userQuizSelections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEstCorrecte(): ?bool
    {
        return $this->estCorrecte;
    }

    public function setEstCorrecte(bool $estCorrecte): self
    {
        $this->estCorrecte = $estCorrecte;
        return $this;
    }

    public function getQuestion(): ?QuizQuestion
    {
        return $this->question;
    }

    public function setQuestion(?QuizQuestion $question): self
    {
        $this->question = $question;
        return $this;
    }

    /**
     * @return Collection<int, UserQuizSelection>
     */
    public function getUserQuizSelections(): Collection
    {
        return $this->userQuizSelections;
    }

    public function addUserQuizSelection(UserQuizSelection $userQuizSelection): static
    {
        if (!$this->userQuizSelections->contains($userQuizSelection)) {
            $this->userQuizSelections->add($userQuizSelection);
            $userQuizSelection->addResponse($this);
        }

        return $this;
    }

    public function removeUserQuizSelection(UserQuizSelection $userQuizSelection): static
    {
        if ($this->userQuizSelections->removeElement($userQuizSelection)) {
            $userQuizSelection->removeResponse($this);
        }

        return $this;
    }
}
