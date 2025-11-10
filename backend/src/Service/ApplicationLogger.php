<?php

namespace App\Service;

use App\Entity\ApplicationLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service ApplicationLogger - Centralise la création des logs d'audit
 * 
 * Utilisation :
 * $this->logger->logUserLogin($user, $request);
 * $this->logger->logTicketCreated($user, $ticket);
 * $this->logger->logTicketAssigned($user, $ticket, $assignee);
 */
class ApplicationLogger
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack
    ) {
    }

    /**
     * Méthode générique pour créer un log
     */
    private function createLog(
        string $action,
        ?User $user = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $details = null
    ): void {
        $log = new ApplicationLog();
        $log->setAction($action);
        $log->setUser($user);
        $log->setEntityType($entityType);
        $log->setEntityId($entityId);
        $log->setDetails($details);

        // Récupérer les infos de la requête HTTP si disponible
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $log->setIpAddress($request->getClientIp());
            $log->setUserAgent($request->headers->get('User-Agent'));
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Log connexion utilisateur
     */
    public function logUserLogin(User $user): void
    {
        $this->createLog(
            action: 'USER_LOGIN',
            user: $user,
            entityType: 'User',
            entityId: $user->getId(),
            details: [
                'user_name' => $user->getName(),
                'user_role' => $user->getRole()
            ]
        );
    }

    /**
     * Log déconnexion utilisateur
     */
    public function logUserLogout(User $user): void
    {
        $this->createLog(
            action: 'USER_LOGOUT',
            user: $user,
            entityType: 'User',
            entityId: $user->getId(),
            details: [
                'user_name' => $user->getName()
            ]
        );
    }

    /**
     * Log création de ticket
     */
    public function logTicketCreated(User $creator, int $ticketId, string $title, string $priority): void
    {
        $this->createLog(
            action: 'TICKET_CREATED',
            user: $creator,
            entityType: 'Ticket',
            entityId: $ticketId,
            details: [
                'ticket_title' => $title,
                'priority' => $priority,
                'creator_name' => $creator->getName()
            ]
        );
    }

    /**
     * Log assignation de ticket
     */
    public function logTicketAssigned(User $assignee, int $ticketId, string $ticketTitle): void
    {
        $this->createLog(
            action: 'TICKET_ASSIGNED',
            user: $assignee,
            entityType: 'Ticket',
            entityId: $ticketId,
            details: [
                'ticket_title' => $ticketTitle,
                'assignee_id' => $assignee->getId(),
                'assignee_name' => $assignee->getName()
            ]
        );
    }

    /**
     * Log changement de statut
     */
    public function logTicketStatusChanged(User $user, int $ticketId, string $oldStatus, string $newStatus): void
    {
        $this->createLog(
            action: 'TICKET_STATUS_CHANGED',
            user: $user,
            entityType: 'Ticket',
            entityId: $ticketId,
            details: [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $user->getName()
            ]
        );
    }

    /**
     * Log changement de priorité
     */
    public function logTicketPriorityChanged(User $user, int $ticketId, string $oldPriority, string $newPriority): void
    {
        $this->createLog(
            action: 'TICKET_PRIORITY_CHANGED',
            user: $user,
            entityType: 'Ticket',
            entityId: $ticketId,
            details: [
                'old_priority' => $oldPriority,
                'new_priority' => $newPriority,
                'changed_by' => $user->getName()
            ]
        );
    }

    /**
     * Log création de notification
     */
    public function logNotificationCreated(User $recipient, string $notificationType, int $notificationId): void
    {
        $this->createLog(
            action: 'NOTIFICATION_CREATED',
            user: $recipient,
            entityType: 'Notification',
            entityId: $notificationId,
            details: [
                'notification_type' => $notificationType,
                'recipient_name' => $recipient->getName()
            ]
        );
    }

    /**
     * Log lecture de notification
     */
    public function logNotificationRead(User $user, int $notificationId): void
    {
        $this->createLog(
            action: 'NOTIFICATION_READ',
            user: $user,
            entityType: 'Notification',
            entityId: $notificationId,
            details: [
                'user_name' => $user->getName()
            ]
        );
    }

    /**
     * Log erreur système
     */
    public function logError(string $errorMessage, ?User $user = null, ?array $context = null): void
    {
        $this->createLog(
            action: 'ERROR',
            user: $user,
            details: [
                'error_message' => $errorMessage,
                'context' => $context,
                'user_name' => $user?->getName()
            ]
        );
    }
}
