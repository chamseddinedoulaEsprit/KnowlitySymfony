<?php
namespace App\Entity;

use App\Repository\ResultatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResultatRepository::class)]
class Resultat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Positive(message: "Le score doit être strictement positif.")]
    private ?int $score = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de soumission est obligatoire.")]
    #[Assert\GreaterThanOrEqual(
        value: "today",
        message: "La date de soumission ne peut pas être antérieure à aujourd'hui."
    )]
    private ?\DateTimeInterface $submitted_at = null;

    #[ORM\Column(length: 255)]
    private ?string $feedback = null;

    #[ORM\OneToMany(mappedBy: 'resultat', targetEntity: Reponse::class, cascade: ['persist', 'remove'])]
    private $reponses;

    public function __construct()
    {
        $this->submitted_at = new \DateTime(); // Définit la date de soumission par défaut à la date actuelle
        $this->reponses = new \Doctrine\Common\Collections\ArrayCollection(); // Initialiser la collection de réponses
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;
        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeInterface
    {
        return $this->submitted_at;
    }

    public function setSubmittedAt(\DateTimeInterface $submitted_at): static
    {
        $this->submitted_at = $submitted_at;
        return $this;
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setFeedback(string $feedback): static
    {
        $this->feedback = $feedback;
        return $this;
    }

    public function getReponses()
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses[] = $reponse;
            $reponse->setResultat($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        $this->reponses->removeElement($reponse);
        if ($reponse->getResultat() === $this) {
            $reponse->setResultat(null);
        }

        return $this;
    }
}