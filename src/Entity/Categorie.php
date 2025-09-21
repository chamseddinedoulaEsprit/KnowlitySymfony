<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la catégorie est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $descrption = null;

    #[ORM\Column(length: 255)]
    
    private ?string $icone = null;

    #[ORM\OneToMany(targetEntity: Matiere::class, mappedBy: 'categorie')]
    private Collection $matieres;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Les mots-clés sont obligatoires")]
    #[Assert\Length(max: 255)]
    private ?string $mots_cles = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le public cible est obligatoire")]
    #[Assert\Choice(
        choices: ['élèves', 'étudiants', 'adultes', 'professionnels'],
        message: "Choix invalide pour le public cible"
    )]
    private ?string $public_cible = null;


    public function __construct()
    {
        $this->matieres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescrption(): ?string
    {
        return $this->descrption;
    }

    public function setDescrption(string $descrption): static
    {
        $this->descrption = $descrption;

        return $this;
    }

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function setIcone(string $icone): static
    {
        $this->icone = $icone;

        return $this;
    }

    /**
     * @return Collection<int, Matiere>
     */
    public function getMatieres(): Collection
    {
        return $this->matieres;
    }

    public function addMatiere(Matiere $matiere): static
    {
        if (!$this->matieres->contains($matiere)) {
            $this->matieres->add($matiere);
            $matiere->setCategorie($this);
        }

        return $this;
    }

    public function removeMatiere(Matiere $matiere): static
    {
        if ($this->matieres->removeElement($matiere)) {
            // set the owning side to null (unless already changed)
            if ($matiere->getCategorie() === $this) {
                $matiere->setCategorie(null);
            }
        }

        return $this;
    }

    

    public function getMotsCles(): ?string
    {
        return $this->mots_cles;
    }

    public function setMotsCles(string $mots_cles): static
    {
        $this->mots_cles = $mots_cles;

        return $this;
    }

    public function getPublicCible(): ?string
    {
        return $this->public_cible;
    }

    public function setPublicCible(string $public_cible): static
    {
        $this->public_cible = $public_cible;

        return $this;
    }
}
