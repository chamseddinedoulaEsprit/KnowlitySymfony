<?php

namespace App\Entity;

use App\Repository\EventsRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 5000)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $start_date = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\GreaterThan(['propertyPath' => 'start_date'])]
    private ?\DateTime $end_date = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $max_participants = null;

    #[ORM\Column(nullable: true)]
    private ?int $seats_available = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTime $created_at = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\OneToMany(mappedBy: "events", targetEntity: EventRegistration::class, orphanRemoval: true)]
    private Collection $registrations;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $organizer = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    /**
     * @var Collection<int, UserEventPreference>
     */
    #[ORM\OneToMany(targetEntity: UserEventPreference::class, mappedBy: 'event')]
    private Collection $userEventPreferences;

    public function __construct()
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTime();
        }
        $this->registrations = new ArrayCollection();
        $this->userEventPreferences = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getStartDate(): ?\DateTime { return $this->start_date; }
    public function setStartDate(?\DateTime $start_date): static { $this->start_date = $start_date; return $this; }
    public function getEndDate(): ?\DateTime { return $this->end_date; }
    public function setEndDate(?\DateTime $end_date): static { $this->end_date = $end_date; return $this; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getMaxParticipants(): ?int { return $this->max_participants; }
    public function setMaxParticipants(?int $max_participants): static { $this->max_participants = $max_participants; return $this; }
   
    public function getSeatsAvailable(): ?int { return $this->seats_available; }
    public function setSeatsAvailable(?int $seats_available): static { $this->seats_available = $seats_available; return $this; }
   
    public function getLocation(): ?string { return $this->location; }
    public function setLocation(?string $location): static { $this->location = $location; return $this; }
    public function getCreatedAt(): ?\DateTime { return $this->created_at; }
    public function setCreatedAt(\DateTime $created_at): static { $this->created_at = $created_at; return $this; }
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }
    public function getCategory(): ?string { return $this->category; }
    public function setCategory(string $category): static { $this->category = $category; return $this; }

    public function getOrganizer(): ?User { return $this->organizer; }
    public function setOrganizer(?User $organizer): self { $this->organizer = $organizer; return $this; }

    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

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
            $userEventPreference->setEvent($this);
        }

        return $this;
    }

    public function removeUserEventPreference(UserEventPreference $userEventPreference): static
    {
        if ($this->userEventPreferences->removeElement($userEventPreference)) {
            // set the owning side to null (unless already changed)
            if ($userEventPreference->getEvent() === $this) {
                $userEventPreference->setEvent(null);
            }
        }

        return $this;
    }


}
