<?php
namespace App\Entity;
use Algolia\SearchBundle\Annotation\Property;
use App\Repository\MatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MatiereRepository::class)]

class Matiere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $titre = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de création ne peut pas être vide.")]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de mise à jour ne peut pas être vide.")]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'matieres')]
    #[Assert\NotNull(message: "La catégorie ne peut pas être vide.")]
    private ?Categorie $categorie = null;

    /**
     * @var Collection<int, Cours>
     */
    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'matiere')]
    private Collection $cours;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Les prérequis ne peuvent pas être vides.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Les prérequis ne peuvent pas dépasser {{ limit }} caractères."
    )]
    private ?string $prerequis = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La couleur du thème ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: '/^#[0-9A-Fa-f]{6}$/',
        message: "La couleur du thème doit être un code hexadécimal valide (par ex. #FF5733)."
    )]
    private ?string $couleur_theme = null;
    
    public function __construct()
    {
        $this->cours = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable(); // Définir la date de création par défaut
        $this->updated_at = new \DateTimeImmutable(); // Définir la date de mise à jour par défaut
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, Cours>
     */
    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCour(Cours $cour): static
    {
        if (!$this->cours->contains($cour)) {
            $this->cours->add($cour);
            $cour->setMatiere($this);
        }

        return $this;
    }

    public function removeCour(Cours $cour): static
    {
        if ($this->cours->removeElement($cour)) {
            // set the owning side to null (unless already changed)
            if ($cour->getMatiere() === $this) {
                $cour->setMatiere(null);
            }
        }

        return $this;
    }

    public function getPrerequis(): ?string
    {
        return $this->prerequis;
    }

    public function setPrerequis(string $prerequis): static
    {
        $this->prerequis = $prerequis;

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

    public function getCouleurTheme(): ?string
    {
        return $this->couleur_theme;
    }

    public function setCouleurTheme(string $couleur_theme): static
    {
        $this->couleur_theme = $couleur_theme;

        return $this;
    }
}