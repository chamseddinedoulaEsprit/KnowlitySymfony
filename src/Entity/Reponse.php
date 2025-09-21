<?php
namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $text = null;

    #[ORM\Column]
    private ?bool $is_correct = false; // Valeur par défaut false

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    private ?Question $question = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    private ?Evaluation $evaluation = null;

    #[ORM\ManyToOne(targetEntity: Resultat::class, inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Resultat $resultat = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $note = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $submitTime = null;

    #[ORM\Column(type: 'boolean')]
    private bool $plagiatSuspect = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $plagiatDetails = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $typingPattern = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = 'non_corrige';

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $correctedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function isCorrect(): ?bool
    {
        return $this->is_correct;
    }

    public function setIsCorrect(bool $is_correct): static
    {
        $this->is_correct = $is_correct;
        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
        return $this;
    }

    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluation $evaluation): static
    {
        $this->evaluation = $evaluation;
        return $this;
    }

    public function getResultat(): ?Resultat
    {
        return $this->resultat;
    }

    public function setResultat(?Resultat $resultat): static
    {
        $this->resultat = $resultat;
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

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getSubmitTime(): ?\DateTimeInterface
    {
        return $this->submitTime;
    }

    public function setSubmitTime(?\DateTimeInterface $submitTime): self
    {
        $this->submitTime = $submitTime;
        return $this;
    }

    public function isPlagiatSuspect(): bool
    {
        return $this->plagiatSuspect;
    }

    public function setPlagiatSuspect(bool $plagiatSuspect): self
    {
        $this->plagiatSuspect = $plagiatSuspect;
        return $this;
    }

    public function getPlagiatDetails(): ?string
    {
        return $this->plagiatDetails;
    }

    public function setPlagiatDetails(?string $plagiatDetails): self
    {
        $this->plagiatDetails = $plagiatDetails;
        return $this;
    }

    public function getTypingPattern(): ?array
    {
        return $this->typingPattern;
    }

    public function setTypingPattern(?array $typingPattern): self
    {
        $this->typingPattern = $typingPattern;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCorrectedAt(): ?\DateTime
    {
        return $this->correctedAt;
    }

    public function setCorrectedAt(?\DateTime $correctedAt): self
    {
        $this->correctedAt = $correctedAt;
        return $this;
    }
}