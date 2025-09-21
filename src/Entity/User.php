<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User  implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(min: 3, minMessage: "Votre nom ne contient pas {{ limit }} caractères .")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Zéèàêïöù]+$/",
        message: "Le nom ne doit pas contenir de chiffres ou de caractères spéciaux."
    )]
    private ?string $nom = null;


    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le prenom ne peut pas être vide.")]
    #[Assert\Length(min: 3, minMessage: "Votre Prenom ne contient pas {{ limit }} caractères .")]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date est obligatoire")]
    #[Assert\LessThanOrEqual(
        value: "today - 10 years",
        message: "Vous devez avoir au moins 10 ans."
    )]
    private ?\DateTimeInterface $date_naissance = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(
        message: "L'email '{{ value }}' n'est pas valide.",
        mode: "strict"
    )]
    #[Assert\Regex(
        pattern: "/@(gmail\.com|esprit\.tn)$/",
        message: "L'email doit se terminer par @gmail.com ou @esprit.tn"
    )]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le numéro de téléphone est obligatoire.")]
    #[Assert\Positive(message: "Le numéro de téléphone doit être un nombre positif.")]
    #[Assert\Length(
        min: 8,
        max: 8,
        exactMessage: "Le numéro de téléphone doit contenir exactement {{ limit }} chiffres."
    )]
    #[Assert\Type(
        type: "integer",
        message: "Le numéro de téléphone doit contenir uniquement des chiffres."
    )]
    private ?int $num_telephone = null;

    #[ORM\Column(length: 254)]
    #[Assert\NotBlank(message: "Password est obligatoire.")]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $image = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le genre est obligatoire.")]
    private ?string $genre = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "La localisation est obligatoire.")]
    private ?string $localisation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $last_login = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Password est obligatoire.")]
    private ?string $confirm_password = null;


    #[ORM\Column(length: 30, nullable: true)]
    private ?string $verification_code = null;

    #[ORM\Column]
    private ?int $banned = null;

    #[ORM\Column]
    private ?int $deleted = null;

    #[ORM\Column(nullable: true)]
    private ?int $grade_level = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Spécialité ne peut pas être vide.")]
    private ?string $specialite = null;

    #[ORM\Column(length: 30)]
    private ?string $roles = null;

    /**
     * @var Collection<int, EventRegistration>
     */
    #[ORM\OneToMany(targetEntity: EventRegistration::class, mappedBy: 'User')]
    private Collection $eventRegistrations;

    /**
     * @var Collection<int, UserEventPreference>
     */
    #[ORM\OneToMany(targetEntity: UserEventPreference::class, mappedBy: 'user')]
    private Collection $userEventPreferences;
 /**
     * @var Collection<int, Cours>
     */
    #[ORM\ManyToMany(targetEntity: Cours::class, mappedBy: 'etudiants')]
    private Collection $cours;

    /**
     * @var Collection<int, Cours>
     */
    #[ORM\ManyToMany(targetEntity: Cours::class, mappedBy: 'etudiantsfavoris')]
    private Collection $coursFavoris;

    /**
     * @var Collection<int, Blog>
     */
 
     #[ORM\ManyToMany(targetEntity: Blog::class, mappedBy: 'Likes')]
     private Collection $likedBlogs;
     
    public function __construct()
    { $this->cours = new ArrayCollection();
        $this->coursFavoris = new ArrayCollection();
        $this->date_naissance = new \DateTime();
        $this->eventRegistrations = new ArrayCollection();
        $this->userEventPreferences = new ArrayCollection();
        $this->likedBlogs = new ArrayCollection(); 
       
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->date_naissance;
    }

    public function setDateNaissance(\DateTimeInterface $date_naissance): static
    {
        $this->date_naissance = $date_naissance;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNumTelephone(): ?int
    {
        return $this->num_telephone;
    }

    public function setNumTelephone(int $num_telephone): static
    {
        $this->num_telephone = $num_telephone;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

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

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->last_login;
    }

    public function setLastLogin(?\DateTimeInterface $last_login): static
    {
        $this->last_login = $last_login;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(string $confirm_password): static
    {
        $this->confirm_password = $confirm_password;

        return $this;
    }

    public function getBanned(): ?int
    {
        return $this->banned;
    }

    public function setBanned(int $banned): static
    {
        $this->banned = $banned;

        return $this;
    }

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(int $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getGradeLevel(): ?int
    {
        return $this->grade_level;
    }

    public function setGradeLevel(?int $grade_level): static
    {
        $this->grade_level = $grade_level;

        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    public function getRoles(): ?string
    {
        return $this->roles;
    }

    public function setRoles(string $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

 /**
     * @return Collection<int, Cours>
     */
    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCour(Cours $cour): self
    {
        if (!$this->cours->contains($cour)) {
            $this->cours->add($cour);
            $cour->addEtudiant($this);
        }

        return $this;
    }

    public function removeCour(Cours $cour): self
    {
        if ($this->cours->removeElement($cour)) {
            $cour->removeEtudiant($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Cours>
     */
    public function getCoursFavoris(): Collection
    {
        return $this->coursFavoris;
    }

    public function addCoursFavori(Cours $coursFavori): self
    {
        if (!$this->coursFavoris->contains($coursFavori)) {
            $this->coursFavoris->add($coursFavori);
            $coursFavori->addEtudiantFavoris($this);
        }

        return $this;
    }

    public function removeCoursFavori(Cours $coursFavori): self
    {
        if ($this->coursFavoris->removeElement($coursFavori)) {
            $coursFavori->removeEtudiantFavoris($this);
        }

        return $this;
    }



    public function getVerificationCode(): ?string
    {
        return $this->verification_code;
    }

    public function setVerificationCode(?string $verification_code): static
    {
        $this->verification_code = $verification_code;

        return $this;
    }






    /**
     * @return Collection<int, EventRegistration>
     */
    public function getEventRegistrations(): Collection
    {
        return $this->eventRegistrations;
    }

    public function addEventRegistration(EventRegistration $eventRegistration): static
    {
        if (!$this->eventRegistrations->contains($eventRegistration)) {
            $this->eventRegistrations->add($eventRegistration);
            $eventRegistration->setUser($this);
        }

        return $this;
    }

    public function removeEventRegistration(EventRegistration $eventRegistration): static
    {
        if ($this->eventRegistrations->removeElement($eventRegistration)) {
            // set the owning side to null (unless already changed)
            if ($eventRegistration->getUser() === $this) {
                $eventRegistration->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserEventPreference>
     */
    public function getUserEventPreferences(): Collection
    {
        return $this->userEventPreferences;
    }

    public function addUserEventPreference(UserEventPreference $userEventPreference): static
    {
        if (!$this->userEventPreferences->contains($userEventPreference)) {
            $this->userEventPreferences->add($userEventPreference);
            $userEventPreference->setUser($this);
        }

        return $this;
    }

    public function removeUserEventPreference(UserEventPreference $userEventPreference): static
    {
        if ($this->userEventPreferences->removeElement($userEventPreference)) {
            // set the owning side to null (unless already changed)
            if ($userEventPreference->getUser() === $this) {
                $userEventPreference->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Blog>
     */
    public function getBlogs(): Collection
    {
        return $this->likedBlogs;
    }

    public function addBlog(Blog $blog): static
    {
        if (!$this->likedBlogs->contains($blog)) {
            $this->likedBlogs->add($blog);
            $blog->addLike($this);
        }

        return $this;
    }

    public function removeBlog(Blog $blog): static
    {
        if ($this->likedBlogs->removeElement($blog)) {
            $blog->removeLike($this);
        }

        return $this;
    }


   

    

   }

