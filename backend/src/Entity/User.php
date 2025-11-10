<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entité User - Représente un utilisateur du système
 * 
 * Rôles disponibles :
 * - CLIENT : Crée des tickets, peut les consulter et commenter
 * - AGENT : Traite les tickets assignés, peut les résoudre
 * - MANAGER : Supervise, assigne les tickets, accès complet
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ticket:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['ticket:read', 'user:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['ticket:read', 'user:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    #[Groups(['ticket:read', 'user:read'])]
    private ?string $role = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['user:read'])]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, Ticket>
     * Tickets créés par cet utilisateur
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'creator')]
    private Collection $createdTickets;

    /**
     * @var Collection<int, Ticket>
     * Tickets assignés à cet utilisateur (agents/managers)
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'assignee')]
    private Collection $assignedTickets;

    /**
     * @var Collection<int, TicketLog>
     * Logs d'actions effectuées par cet utilisateur
     */
    #[ORM\OneToMany(targetEntity: TicketLog::class, mappedBy: 'user')]
    private Collection $ticketLogs;

    /**
     * @var Collection<int, Notification>
     * Notifications reçues par cet utilisateur
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user', cascade: ['remove'])]
    private Collection $notifications;

    public function __construct()
    {
        $this->createdTickets = new ArrayCollection();
        $this->assignedTickets = new ArrayCollection();
        $this->ticketLogs = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getCreatedTickets(): Collection
    {
        return $this->createdTickets;
    }

    public function addCreatedTicket(Ticket $createdTicket): static
    {
        if (!$this->createdTickets->contains($createdTicket)) {
            $this->createdTickets->add($createdTicket);
            $createdTicket->setCreator($this);
        }

        return $this;
    }

    public function removeCreatedTicket(Ticket $createdTicket): static
    {
        if ($this->createdTickets->removeElement($createdTicket)) {
            // set the owning side to null (unless already changed)
            if ($createdTicket->getCreator() === $this) {
                $createdTicket->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getAssignedTickets(): Collection
    {
        return $this->assignedTickets;
    }

    public function addAssignedTicket(Ticket $assignedTicket): static
    {
        if (!$this->assignedTickets->contains($assignedTicket)) {
            $this->assignedTickets->add($assignedTicket);
            $assignedTicket->setAssignee($this);
        }

        return $this;
    }

    public function removeAssignedTicket(Ticket $assignedTicket): static
    {
        if ($this->assignedTickets->removeElement($assignedTicket)) {
            if ($assignedTicket->getAssignee() === $this) {
                $assignedTicket->setAssignee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TicketLog>
     */
    public function getTicketLogs(): Collection
    {
        return $this->ticketLogs;
    }

    public function addTicketLog(TicketLog $ticketLog): static
    {
        if (!$this->ticketLogs->contains($ticketLog)) {
            $this->ticketLogs->add($ticketLog);
            $ticketLog->setUser($this);
        }

        return $this;
    }

    public function removeTicketLog(TicketLog $ticketLog): static
    {
        if ($this->ticketLogs->removeElement($ticketLog)) {
            if ($ticketLog->getUser() === $this) {
                $ticketLog->setUser(null);
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

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }
}
