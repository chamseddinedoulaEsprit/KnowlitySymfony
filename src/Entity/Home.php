<?php

namespace App\Entity;

use App\Repository\HomeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HomeRepository::class)]
class Home
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $chams = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChams(): ?string
    {
        return $this->chams;
    }

    public function setChams(string $chams): static
    {
        $this->chams = $chams;

        return $this;
    }
}
