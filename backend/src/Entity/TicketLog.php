<?php

namespace App\Entity;

use App\Repository\TicketLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité TicketLog - Historise toutes les actions sur un ticket
 * 
 * Actions possibles :
 * - CREATED : Ticket créé
 * - ASSIGNED : Ticket assigné à un agent
 * - STATUS_CHANGED : Changement de statut
 * - PRIORITY_CHANGED : Changement de priorité
 * - COMMENT_ADDED : Commentaire ajouté
 * - CLOSED : Ticket fermé
 * 
 * Le champ 'payload' (JSON) contient les détails de l'action :
 * - Pour ASSIGNED : {"assignee_id": 5, "assignee_name": "John Doe"}
 * - Pour STATUS_CHANGED : {"old_status": "OPEN", "new_status": "IN_PROGRESS"}
 * - Pour COMMENT_ADDED : {"comment": "Texte du commentaire"}
 */
#[ORM\Entity(repositoryClass: TicketLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
class TicketLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ticket $ticket = null;

    #[ORM\ManyToOne(inversedBy: 'ticketLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    private ?string $action = null;

    /**
     * Payload JSON contenant les détails de l'action
     * Utilise le type JSON natif de PostgreSQL
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $payload = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): static
    {
        $this->payload = $payload;

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
}
