<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\BlogRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Comment;
use App\Entity\User;

#[ORM\Entity(repositoryClass: BlogRepository::class)]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu ne peut pas être vide")]
    #[Assert\Length(
        min: 10,
        minMessage: "Le contenu doit contenir au moins {{ limit }} caractères"
    )]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du créateur ne peut pas être vide")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $creatorName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL de l'image n'est pas valide")]
    private ?string $userImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $blogImage = null;

    #[ORM\OneToMany(mappedBy: 'blog', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'likedBlogs')]
    private Collection $Likes;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->comments = new ArrayCollection();
        $this->Likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getCreatorName(): ?string
    {
        return $this->creatorName;
    }

    public function setCreatorName(?string $creatorName): self
    {
        $this->creatorName = $creatorName;
        return $this;
    }

    public function getUserImage(): ?string
    {
        return $this->userImage;
    }

    public function setUserImage(?string $userImage): self
    {
        $this->userImage = $userImage;
        return $this;
    }

    public function getBlogImage(): ?string
    {
        return $this->blogImage;
    }

    public function setBlogImage(?string $blogImage): self
    {
        $this->blogImage = $blogImage;
        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setBlog($this);
        }
        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getBlog() === $this) {
                $comment->setBlog(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLikedBy(): Collection
    {
        return $this->Likes;
    }

    public function isLikedByUser(int $userId): bool
    {
        foreach ($this->Likes as $user) {
            if ($user->getId() === $userId) {
                return true;
            }
        }
        return false;
    }

    public function addLike(User $user): self
    {
        if (!$this->Likes->contains($user)) {
            $this->Likes[] = $user;
        }
        return $this;
    }

    public function removeLike(User $user): self
    {
        $this->Likes->removeElement($user);
        return $this;
    }

    public function getLikesCount(): int
    {
        return $this->Likes->count();
    }
}
