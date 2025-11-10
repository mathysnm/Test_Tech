<?php

namespace App\Entity;

use App\Repository\ApplicationLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité ApplicationLog - Journal d'audit général de l'application
 * 
 * Types d'actions tracées :
 * - USER_LOGIN : Connexion utilisateur
 * - USER_LOGOUT : Déconnexion utilisateur
 * - TICKET_CREATED : Création de ticket
 * - TICKET_ASSIGNED : Assignation de ticket
 * - TICKET_STATUS_CHANGED : Changement de statut
 * - TICKET_PRIORITY_CHANGED : Changement de priorité
 * - NOTIFICATION_CREATED : Notification créée
 * - NOTIFICATION_READ : Notification lue
 * - ERROR : Erreur système
 * 
 * Le champ 'details' (JSON) contient les informations contextuelles :
 * - Pour USER_LOGIN : {"ip": "192.168.1.1", "user_agent": "..."}
 * - Pour TICKET_CREATED : {"ticket_id": 10, "title": "...", "priority": "HIGH"}
 * - Pour TICKET_ASSIGNED : {"ticket_id": 10, "assignee_id": 5, "assignee_name": "John"}
 * - Pour TICKET_STATUS_CHANGED : {"ticket_id": 10, "old_status": "OPEN", "new_status": "IN_PROGRESS"}
 */
#[ORM\Entity(repositoryClass: ApplicationLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(columns: ['action'], name: 'idx_action')]
#[ORM\Index(columns: ['created_at'], name: 'idx_created_at')]
class ApplicationLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Utilisateur qui a effectué l'action (nullable pour actions système)
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * Type d'action effectuée
     */
    #[ORM\Column(length: 100)]
    private ?string $action = null;

    /**
     * Entité concernée (ex: 'Ticket', 'User', 'Notification')
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $entityType = null;

    /**
     * ID de l'entité concernée
     */
    #[ORM\Column(nullable: true)]
    private ?int $entityId = null;

    /**
     * Détails JSON de l'action
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $details = null;

    /**
     * Adresse IP de l'utilisateur (pour les connexions)
     */
    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    /**
     * User Agent du navigateur (pour les connexions)
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(?string $entityType): static
    {
        $this->entityType = $entityType;
        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): static
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function setDetails(?array $details): static
    {
        $this->details = $details;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
