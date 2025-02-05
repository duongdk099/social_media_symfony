<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['post:read','report:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['post:read','report:read'])]
    private ?string $reason = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['report:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['report:read'])]
    #[ORM\Column(type: 'uuid')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['report:read'])]
    private ?Post $post = null;

    #[ORM\ManyToOne(targetEntity: Comment::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['report:read'])]
    private ?Comment $comment = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
