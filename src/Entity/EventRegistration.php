<?php

namespace App\Entity;
use App\Entity\Events;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
class EventRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Events::class, inversedBy: "registrations")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Events $event = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $registrationDate = null;

    
    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'pending';

    #[ORM\Column(type: 'boolean')]
    private ?bool $disabled_parking = null;

    #[ORM\Column(length: 255)]
    private ?string $coming_from = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;
    
    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'integer', message: 'Only whole numbers are allowed.')]
    #[Assert\Positive(message: 'The number of places must be positive.')]
    private ?int $places_reserved = null;



    public function __construct()
    {
        if ($this->registrationDate === null) {
            $this->registrationDate = new \DateTime();
        }
    }
    // Getters and Setters
    public function getId(): ?int { return $this->id; }

    public function getEvent(): ?Events { return $this->event; }
    public function setEvent(?Events $event): self { $this->event = $event; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }

    public function getRegistrationDate(): ?\DateTimeInterface { return $this->registrationDate; }
    public function setRegistrationDate(\DateTimeInterface $registrationDate): self {
        $this->registrationDate = $registrationDate;
        return $this;
    }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }


    public function getDisabledParking(): ?bool
    {
        return $this->disabled_parking;
    }
    
    public function setDisabledParking(bool $disabled_parking): self
    {
        $this->disabled_parking = $disabled_parking;
        return $this;
    }

    public function getComingFrom(): ?string
    {
        return $this->coming_from;
    }

    public function setComingFrom(string $coming_from): static
    {
        $this->coming_from = $coming_from;

        return $this;
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
    public function getPlacesReserved(): ?int { return $this->places_reserved; }
    public function setPlacesReserved(?int $places_reserved): static { $this->places_reserved = $places_reserved; return $this; }

}
