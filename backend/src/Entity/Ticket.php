<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entité Ticket - Représente un ticket de support
 * 
 * Statuts disponibles :
 * - OPEN : Nouveau ticket, en attente d'assignation
 * - IN_PROGRESS : Assigné à un agent, en cours de traitement
 * - RESOLVED : Résolu par l'agent, en attente de confirmation client
 * - CLOSED : Fermé définitivement
 * 
 * Priorités :
 * - HIGH : Urgent, bloquant
 * - MEDIUM : Important, à traiter rapidement
 * - LOW : Peut attendre
 */
#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ticket:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['ticket:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['ticket:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Groups(['ticket:read'])]
    private ?string $priority = null;

    #[ORM\Column(length: 50)]
    #[Groups(['ticket:read'])]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'createdTickets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['ticket:read'])]
    private ?User $creator = null;

    #[ORM\ManyToOne(inversedBy: 'assignedTickets')]
    #[Groups(['ticket:read'])]
    private ?User $assignee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['ticket:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['ticket:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['ticket:read'])]
    private ?\DateTimeInterface $closedAt = null;

    /**
     * @var Collection<int, TicketLog>
     * Historique des actions sur ce ticket
     */
    #[ORM\OneToMany(targetEntity: TicketLog::class, mappedBy: 'ticket', cascade: ['remove'])]
    #[Groups(['ticket:detail'])]
    private Collection $logs;

    public function __construct()
    {
        $this->logs = new ArrayCollection();
        $this->status = 'OPEN';
        $this->priority = 'MEDIUM';
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function getAssignee(): ?User
    {
        return $this->assignee;
    }

    public function setAssignee(?User $assignee): static
    {
        $this->assignee = $assignee;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): static
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    /**
     * @return Collection<int, TicketLog>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(TicketLog $log): static
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setTicket($this);
        }

        return $this;
    }

    public function removeLog(TicketLog $log): static
    {
        if ($this->logs->removeElement($log)) {
            if ($log->getTicket() === $this) {
                $log->setTicket(null);
            }
        }

        return $this;
    }
}
