<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de gestion des notifications
 * Crée et envoie des notifications aux utilisateurs
 */
class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Crée une notification pour un utilisateur
     * 
     * @param User $user - Utilisateur destinataire
     * @param string $type - Type de notification (TICKET_CREATED, TICKET_ASSIGNED, etc.)
     * @param string $message - Message de la notification
     * @return Notification
     */
    public function createNotification(User $user, string $type, string $message): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setRead(false);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    /**
     * Notifie qu'un nouveau ticket a été créé et assigné
     * Envoie une notification au MANAGER
     * 
     * @param Ticket $ticket
     * @param User $assignedAgent
     * @param User $manager
     */
    public function notifyTicketCreatedAndAssigned(Ticket $ticket, User $assignedAgent, User $manager): void
    {
        $message = sprintf(
            'Nouveau ticket #%d créé par %s et assigné à %s (priorité: %s)',
            $ticket->getId(),
            $ticket->getCreator()->getName(),
            $assignedAgent->getName(),
            $ticket->getPriority()
        );

        $this->createNotification($manager, 'TICKET_CREATED', $message);
    }

    /**
     * Notifie qu'un ticket est passé en cours de traitement
     * Envoie une notification au MANAGER
     * 
     * @param Ticket $ticket
     * @param User $manager
     */
    public function notifyTicketInProgress(Ticket $ticket, User $manager): void
    {
        $message = sprintf(
            'Le ticket #%d est maintenant en cours de traitement par %s',
            $ticket->getId(),
            $ticket->getAssignee()?->getName() ?? 'un agent'
        );

        $this->createNotification($manager, 'TICKET_IN_PROGRESS', $message);
    }

    /**
     * Notifie qu'un ticket a été assigné
     * Envoie une notification à l'AGENT assigné
     * 
     * @param Ticket $ticket
     * @param User $agent
     */
    public function notifyTicketAssigned(Ticket $ticket, User $agent): void
    {
        $message = sprintf(
            'Le ticket #%d vous a été assigné : %s (priorité: %s)',
            $ticket->getId(),
            $ticket->getTitle(),
            $ticket->getPriority()
        );

        $this->createNotification($agent, 'TICKET_ASSIGNED', $message);
    }

    /**
     * Notifie qu'un ticket a changé de statut
     * Envoie une notification au CLIENT créateur du ticket
     * 
     * @param Ticket $ticket
     * @param string $oldStatus
     * @param string $newStatus
     */
    public function notifyTicketStatusChanged(Ticket $ticket, string $oldStatus, string $newStatus): void
    {
        $statusLabels = [
            'OPEN' => 'Ouvert',
            'IN_PROGRESS' => 'En cours',
            'RESOLVED' => 'Résolu',
            'CLOSED' => 'Fermé'
        ];

        $message = sprintf(
            'Le statut de votre ticket #%d "%s" est passé de "%s" à "%s"',
            $ticket->getId(),
            $ticket->getTitle(),
            $statusLabels[$oldStatus] ?? $oldStatus,
            $statusLabels[$newStatus] ?? $newStatus
        );

        $this->createNotification($ticket->getCreator(), 'TICKET_STATUS_CHANGED', $message);
    }

    /**
     * Notifie qu'un ticket a été résolu
     * Envoie une notification au CLIENT créateur du ticket
     * 
     * @param Ticket $ticket
     */
    public function notifyTicketResolved(Ticket $ticket): void
    {
        $message = sprintf(
            'Votre ticket #%d "%s" a été résolu par %s',
            $ticket->getId(),
            $ticket->getTitle(),
            $ticket->getAssignee()?->getName() ?? 'un agent'
        );

        $this->createNotification($ticket->getCreator(), 'TICKET_RESOLVED', $message);
    }

    /**
     * Notifie qu'un ticket a été fermé
     * Envoie une notification au CLIENT créateur du ticket
     * 
     * @param Ticket $ticket
     */
    public function notifyTicketClosed(Ticket $ticket): void
    {
        $message = sprintf(
            'Votre ticket #%d "%s" a été fermé',
            $ticket->getId(),
            $ticket->getTitle()
        );

        $this->createNotification($ticket->getCreator(), 'TICKET_CLOSED', $message);
    }

    /**
     * Notifie qu'un ticket a été réassigné
     * Envoie une notification au nouvel AGENT assigné
     * 
     * @param Ticket $ticket
     * @param User $newAgent
     * @param User|null $oldAgent
     */
    public function notifyTicketReassigned(Ticket $ticket, User $newAgent, ?User $oldAgent = null): void
    {
        if ($oldAgent) {
            $message = sprintf(
                'Le ticket #%d "%s" vous a été réassigné (anciennement assigné à %s)',
                $ticket->getId(),
                $ticket->getTitle(),
                $oldAgent->getName()
            );
        } else {
            $message = sprintf(
                'Le ticket #%d "%s" vous a été assigné',
                $ticket->getId(),
                $ticket->getTitle()
            );
        }

        $this->createNotification($newAgent, 'TICKET_ASSIGNED', $message);
    }
}
