<?php

namespace App\Entity;

use App\Repository\UserEventPreferenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserEventPreferenceRepository::class)]
class UserEventPreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(inversedBy: 'userEventPreferences')]
    private ?Events $event = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column]
    private ?int $preference_score = null;

    #[ORM\ManyToOne(inversedBy: 'userEventPreferences')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEvent(): ?Events
    {
        return $this->event;
    }

    public function setEvent(?Events $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getPreferenceScore(): ?int
    {
        return $this->preference_score;
    }

    public function setPreferenceScore(int $preference_score): static
    {
        $this->preference_score = $preference_score;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
