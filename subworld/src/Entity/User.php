<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;




#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['post:read','subworld:read','user:read','message:read','report:read','notification:read'])]
    private ?UuidInterface $id = null;



    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(['post:read','subworld:read','user:read','message:read','report:read','notification:read'])]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['post:read','user:read'])]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['post:read','comment:read','subworld:read','user:read','message:read','report:read','notification:read'])]
    private ?string $username = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['auth:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['user:read', 'user:write', 'auth:read'])]
    private bool $isVerified = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['auth:write'])]
    private ?string $verificationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['auth:read'])]
    private ?\DateTimeInterface $verifiedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['auth:read'])]
    private ?\DateTimeInterface $verificationTokenExpiresAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class, cascade: ['persist', 'remove'])]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, cascade: ['persist', 'remove'])]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Vote::class, cascade: ['persist', 'remove'])]
    private Collection $votes;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_roles')]
    private Collection $rolesEntities;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Subworld::class, cascade: ['persist', 'remove'])]
    private Collection $ownedSubworlds;

    #[ORM\ManyToMany(targetEntity: Subworld::class, mappedBy: 'members')]
    private Collection $joinedSubworlds;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class, cascade: ['persist', 'remove'])]
    private Collection $sentMessages;

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Message::class, cascade: ['persist', 'remove'])]
    private Collection $receivedMessages;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Report::class, cascade: ['persist', 'remove'])]
    private Collection $reports;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: "boolean")]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->id = Uuid::uuid4(); 
        $this->createdAt = new \DateTime(); 
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->rolesEntities = new ArrayCollection();
        $this->ownedSubworlds = new ArrayCollection();
        $this->joinedSubworlds = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    /**
     * A visual identifier that represents this user.
     */
    public function getUserIdentifier(): string
    {
        return $this->id instanceof UuidInterface ? $this->id->toString() : (string) $this->id;
    }



    public function getRoles(): array
    {
        $roles = $this->rolesEntities->map(fn($role) => $role->getName())->toArray();
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $token): self
    {
        $this->verificationToken = $token;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }


    public function getVerificationTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->verificationTokenExpiresAt;
    }

    public function setVerificationTokenExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->verificationTokenExpiresAt = $expiresAt;
        return $this;
    }

    public function getVerifiedAt(): ?\DateTimeInterface
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?\DateTimeInterface $verifiedAt): self
    {
        $this->verifiedAt = $verifiedAt;
        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

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
            $this->comments[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setUser($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        if ($this->votes->removeElement($vote)) {
            if ($vote->getUser() === $this) {
                $vote->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRolesEntities(): Collection
    {
        return $this->rolesEntities;
    }

    public function addRoleEntity(Role $role): self
    {
        if (!$this->rolesEntities->contains($role)) {
            $this->rolesEntities[] = $role;
        }
        return $this;
    }


    public function removeRoleEntity(Role $role): self
    {
        if ($this->rolesEntities->removeElement($role)) {
            $role->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Subworld>
     */
    public function getJoinedSubworlds(): Collection
    {
        return $this->joinedSubworlds;
    }

    public function addJoinedSubworld(Subworld $subworld): self
    {
        if (!$this->joinedSubworlds->contains($subworld)) {
            $this->joinedSubworlds[] = $subworld;
            $subworld->addMember($this);
        }

        return $this;
    }

    public function removeJoinedSubworld(Subworld $subworld): self
    {
        if ($this->joinedSubworlds->removeElement($subworld)) {
            $subworld->removeMember($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Subworld>
     */
    public function getOwnedSubworlds(): Collection
    {
        return $this->ownedSubworlds;
    }

    public function addOwnedSubworld(Subworld $subworld): self
    {
        if (!$this->ownedSubworlds->contains($subworld)) {
            $this->ownedSubworlds[] = $subworld;
            $subworld->setOwner($this);
        }

        return $this;
    }

    public function removeOwnedSubworld(Subworld $subworld): self
    {
        if ($this->ownedSubworlds->removeElement($subworld)) {
            if ($subworld->getOwner() === $this) {
                $subworld->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function addSentMessage(Message $message): self
    {
        if (!$this->sentMessages->contains($message)) {
            $this->sentMessages[] = $message;
            $message->setSender($this);
        }

        return $this;
    }

    public function removeSentMessage(Message $message): self
    {
        if ($this->sentMessages->removeElement($message)) {
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function addReceivedMessage(Message $message): self
    {
        if (!$this->receivedMessages->contains($message)) {
            $this->receivedMessages[] = $message;
            $message->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Message $message): self
    {
        if ($this->receivedMessages->removeElement($message)) {
            if ($message->getReceiver() === $this) {
                $message->setReceiver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setUser($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->removeElement($report)) {
            if ($report->getUser() === $this) {
                $report->setUser(null);
            }
        }

        return $this;
    }

    public function eraseCredentials(): void {}

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
