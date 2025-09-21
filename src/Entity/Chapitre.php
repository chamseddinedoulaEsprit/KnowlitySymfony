<?php

namespace App\Entity;

use App\Repository\ChapitreRepository;
use App\Validator\Constraints\UniqueChapterOrder;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChapitreRepository::class)]
#[UniqueChapterOrder] // Contrainte personnalisée pour l'unicité de l'ordre dans un cours
class Chapitre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $title = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "L'ordre du chapitre ne peut pas être vide.")]
    #[Assert\PositiveOrZero(message: "L'ordre du chapitre doit être un nombre positif ou zéro.")]
    #[Assert\Type(
        type: 'integer',
        message: "L'ordre du chapitre doit être un nombre entier."
    )]
    private ?int $chapOrder = null;

    #[ORM\ManyToOne(inversedBy: 'chapitres')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "Le cours associé ne peut pas être vide.")]
    private ?Cours $cours = null;

    #[ORM\Column(length: 255)]
    
    private ?string $contenu = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La durée estimée ne peut pas être vide.")]
    #[Assert\PositiveOrZero(message: "La durée estimée doit être un nombre positif ou zéro.")]
    #[Assert\Type(
        type: 'integer',
        message: "La durée estimée doit être un nombre entier."
    )]
    private ?int $duree_estimee = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le nombre de vues ne peut pas être vide.")]
    #[Assert\PositiveOrZero(message: "Le nombre de vues doit être un nombre positif ou zéro.")]
    #[Assert\Type(
        type: 'integer',
        message: "Le nombre de vues doit être un nombre entier."
    )]
    private ?int $nbr_vues = null;
    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
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

    public function getChapOrder(): ?int
    {
        return $this->chapOrder;
    }

    public function setChapOrder(int $chapOrder): static
    {
        $this->chapOrder = $chapOrder;

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

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDureeEstimee(): ?int
    {
        return $this->duree_estimee;
    }

    public function setDureeEstimee(int $duree_estimee): static
    {
        $this->duree_estimee = $duree_estimee;

        return $this;
    }

    

    public function getNbrVues(): ?int
    {
        return $this->nbr_vues;
    }
    public function incrementNbrVues(): self
    {
        $this->nbr_vues ++;

        return $this;
    }
    public function setNbrVues(int $nbr_vues): static
    {
        $this->nbr_vues = $nbr_vues;

        return $this;
    }
}