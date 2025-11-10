<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Service\TicketAssignmentService;
use App\Service\NotificationService;
use App\Service\ApplicationLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/tickets', name: 'api_tickets_')]
class TicketController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TicketRepository $ticketRepository,
        private UserRepository $userRepository,
        private TicketAssignmentService $assignmentService,
        private NotificationService $notificationService,
        private ApplicationLogger $appLogger
    ) {
    }

    /**
     * Liste tous les tickets en fonction du rôle de l'utilisateur
     * Authentification OBLIGATOIRE - user_id requis
     * - CLIENT : Voit uniquement ses tickets (créés par lui)
     * - AGENT : Voit uniquement les tickets qui lui sont assignés
     * - MANAGER : Voit tous les tickets
     * 
     * @return JsonResponse
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // Récupérer l'ID de l'utilisateur depuis les query params
        $userId = $request->query->get('user_id');
        
        // Authentification OBLIGATOIRE
        if (!$userId) {
            return $this->json([
                'success' => false,
                'message' => 'Authentication required. Please provide user_id parameter.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Filtrer les tickets selon le rôle - avec requêtes optimisées
        $tickets = match($user->getRole()) {
            'CLIENT' => $this->ticketRepository->findBy(['creator' => $user]),
            'AGENT' => $this->ticketRepository->findBy(['assignee' => $user]),
            'MANAGER' => $this->ticketRepository->findAll(),
            default => []
        };

        // Construire manuellement la réponse pour éviter la sérialisation lourde
        $ticketsData = array_map(function($ticket) {
            return [
                'id' => $ticket->getId(),
                'title' => $ticket->getTitle(),
                'description' => $ticket->getDescription(),
                'status' => $ticket->getStatus(),
                'priority' => $ticket->getPriority(),
                'creator' => [
                    'id' => $ticket->getCreator()->getId(),
                    'name' => $ticket->getCreator()->getName(),
                    'role' => $ticket->getCreator()->getRole()
                ],
                'assignee' => $ticket->getAssignee() ? [
                    'id' => $ticket->getAssignee()->getId(),
                    'name' => $ticket->getAssignee()->getName(),
                    'role' => $ticket->getAssignee()->getRole()
                ] : null,
                'createdAt' => $ticket->getCreatedAt()->format('c'),
                'updatedAt' => $ticket->getUpdatedAt()->format('c'),
                'closedAt' => $ticket->getClosedAt()?->format('c')
            ];
        }, $tickets);

        return $this->json([
            'success' => true,
            'count' => count($ticketsData),
            'data' => $ticketsData,
            'user_role' => $user->getRole()
        ], Response::HTTP_OK);
    }

    /**
     * Récupère les statistiques des tickets filtrées selon le rôle
     * Authentification OBLIGATOIRE - user_id requis
     * - CLIENT : Statistiques de ses tickets uniquement
     * - AGENT : Statistiques des tickets qui lui sont assignés
     * - MANAGER : Statistiques de tous les tickets
     * 
     * ATTENTION : Cette route doit être AVANT /{id} pour ne pas être capturée
     * 
     * @return JsonResponse
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(Request $request): JsonResponse
    {
        // Récupérer l'ID de l'utilisateur depuis les query params
        $userId = $request->query->get('user_id');
        
        // Authentification OBLIGATOIRE
        if (!$userId) {
            return $this->json([
                'success' => false,
                'message' => 'Authentication required. Please provide user_id parameter.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Construire les critères de base selon le rôle
        $baseCriteria = match($user->getRole()) {
            'CLIENT' => ['creator' => $user],
            'AGENT' => ['assignee' => $user],
            'MANAGER' => [],
            default => ['id' => -1] // Aucun résultat si rôle inconnu
        };

        // Calculer le total
        $total = $this->ticketRepository->count($baseCriteria);
        
        // Statistiques par statut
        $byStatus = [
            'open' => $this->ticketRepository->count(array_merge($baseCriteria, ['status' => 'OPEN'])),
            'in_progress' => $this->ticketRepository->count(array_merge($baseCriteria, ['status' => 'IN_PROGRESS'])),
            'resolved' => $this->ticketRepository->count(array_merge($baseCriteria, ['status' => 'RESOLVED'])),
            'closed' => $this->ticketRepository->count(array_merge($baseCriteria, ['status' => 'CLOSED']))
        ];

        // Statistiques par priorité
        $byPriority = [
            'high' => $this->ticketRepository->count(array_merge($baseCriteria, ['priority' => 'HIGH'])),
            'medium' => $this->ticketRepository->count(array_merge($baseCriteria, ['priority' => 'MEDIUM'])),
            'low' => $this->ticketRepository->count(array_merge($baseCriteria, ['priority' => 'LOW']))
        ];

        return $this->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'by_status' => $byStatus,
                'by_priority' => $byPriority
            ],
            'user_role' => $user->getRole()
        ]);
    }

    /**
     * Récupère un ticket par son ID
     * Authentification OBLIGATOIRE - user_id requis
     * Filtrage par rôle :
     * - CLIENT : Peut voir uniquement ses propres tickets
     * - AGENT : Peut voir uniquement les tickets qui lui sont assignés
     * - MANAGER : Peut voir tous les tickets
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, Request $request): JsonResponse
    {
        // Récupérer l'ID de l'utilisateur depuis les query params
        $userId = $request->query->get('user_id');
        
        // Authentification OBLIGATOIRE
        if (!$userId) {
            return $this->json([
                'success' => false,
                'message' => 'Authentication required. Please provide user_id parameter.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $ticket = $this->ticketRepository->find($id);

        if (!$ticket) {
            return $this->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifier les permissions selon le rôle
        $hasAccess = match($user->getRole()) {
            'CLIENT' => $ticket->getCreator() === $user,
            'AGENT' => $ticket->getAssignee() === $user,
            'MANAGER' => true,
            default => false
        };

        if (!$hasAccess) {
            return $this->json([
                'success' => false,
                'message' => 'Access denied. You do not have permission to view this ticket.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->json([
            'success' => true,
            'data' => $ticket
        ], Response::HTTP_OK, [], [
            'groups' => ['ticket:read', 'ticket:detail']
        ]);
    }

    /**
     * Crée un nouveau ticket avec assignation automatique
     * Authentification OBLIGATOIRE - user_id requis (seuls les CLIENTs peuvent créer)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Authentification OBLIGATOIRE - chercher user_id dans query params d'abord, puis dans body
        $userId = $request->query->get('user_id') ?? $data['user_id'] ?? null;
        if (!$userId) {
            return $this->json([
                'success' => false,
                'message' => 'Authentication required. Please provide user_id.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Seuls les CLIENTs peuvent créer des tickets
        if ($user->getRole() !== 'CLIENT') {
            return $this->json([
                'success' => false,
                'message' => 'Only CLIENT users can create tickets'
            ], Response::HTTP_FORBIDDEN);
        }

        // Validation des champs
        if (!isset($data['title']) || empty(trim($data['title']))) {
            return $this->json([
                'success' => false,
                'message' => 'Title is required and cannot be empty'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['description']) || empty(trim($data['description']))) {
            return $this->json([
                'success' => false,
                'message' => 'Description is required and cannot be empty'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validation de la priorité
        $priority = strtoupper($data['priority'] ?? 'MEDIUM');
        if (!in_array($priority, ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid priority. Must be: LOW, MEDIUM, HIGH, or CRITICAL'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Créer le ticket
        $ticket = new Ticket();
        $ticket->setTitle(trim($data['title']));
        $ticket->setDescription(trim($data['description']));
        $ticket->setPriority($priority);
        $ticket->setStatus('OPEN');
        $ticket->setCreator($user);

        // Assignation automatique à un agent
        $assignedAgent = $this->assignmentService->assignTicket($ticket);

        // Persister le ticket
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        // 📝 LOG: Création du ticket
        $this->appLogger->logTicketCreated($user, $ticket->getId(), $ticket->getTitle(), $ticket->getPriority());

        // 📝 LOG: Assignation si un agent a été trouvé
        if ($assignedAgent) {
            $this->appLogger->logTicketAssigned($assignedAgent, $ticket->getId(), $ticket->getTitle());
        }

        // Créer les notifications
        if ($assignedAgent) {
            // Notifier l'agent assigné
            $this->notificationService->notifyTicketAssigned($ticket, $assignedAgent);
            
            // Notifier le MANAGER qu'un nouveau ticket a été créé et assigné
            $manager = $this->userRepository->findOneBy(['role' => 'MANAGER']);
            if ($manager) {
                $this->notificationService->notifyTicketCreatedAndAssigned($ticket, $assignedAgent, $manager);
            }
        }

        return $this->json([
            'success' => true,
            'message' => $assignedAgent 
                ? 'Ticket created and assigned successfully' 
                : 'Ticket created but no agent available for assignment',
            'data' => [
                'id' => $ticket->getId(),
                'title' => $ticket->getTitle(),
                'description' => $ticket->getDescription(),
                'status' => $ticket->getStatus(),
                'priority' => $ticket->getPriority(),
                'creator' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'role' => $user->getRole()
                ],
                'assignee' => $assignedAgent ? [
                    'id' => $assignedAgent->getId(),
                    'name' => $assignedAgent->getName(),
                    'role' => $assignedAgent->getRole()
                ] : null,
                'createdAt' => $ticket->getCreatedAt()->format('c')
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Met à jour le statut d'un ticket
     * Authentification OBLIGATOIRE - user_id requis
     * Réservé aux AGENT et MANAGER uniquement
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/{id}/status', name: 'update_status', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Authentification OBLIGATOIRE
        $userId = $data['user_id'] ?? null;
        if (!$userId) {
            return $this->json([
                'success' => false,
                'message' => 'Authentication required. Please provide user_id.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Seuls les AGENTs et MANAGERs peuvent modifier le statut
        if (!in_array($user->getRole(), ['AGENT', 'MANAGER'])) {
            return $this->json([
                'success' => false,
                'message' => 'Only AGENT and MANAGER users can update ticket status'
            ], Response::HTTP_FORBIDDEN);
        }

        $ticket = $this->ticketRepository->find($id);
        if (!$ticket) {
            return $this->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Pour un AGENT, vérifier qu'il est bien l'assigné
        if ($user->getRole() === 'AGENT' && $ticket->getAssignee() !== $user) {
            return $this->json([
                'success' => false,
                'message' => 'You can only update status of tickets assigned to you'
            ], Response::HTTP_FORBIDDEN);
        }

        // Validation du nouveau statut
        $newStatus = strtoupper($data['status'] ?? '');
        if (!in_array($newStatus, ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'])) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid status. Must be: OPEN, IN_PROGRESS, RESOLVED, or CLOSED'
            ], Response::HTTP_BAD_REQUEST);
        }

        $oldStatus = $ticket->getStatus();
        $ticket->setStatus($newStatus);

        // Si le ticket passe en RESOLVED ou CLOSED, marquer closedAt
        if (in_array($newStatus, ['RESOLVED', 'CLOSED']) && !$ticket->getClosedAt()) {
            $ticket->setClosedAt(new \DateTime());
        }

        $this->entityManager->flush();

        // 📝 LOG: Changement de statut
        if ($oldStatus !== $newStatus) {
            $this->appLogger->logTicketStatusChanged($user, $ticket->getId(), $oldStatus, $newStatus);
        }

        // Créer les notifications
        if ($oldStatus !== $newStatus) {
            // Notifier le client du changement de statut
            $this->notificationService->notifyTicketStatusChanged($ticket, $oldStatus, $newStatus);
            
            // Notifier le MANAGER si le ticket passe en cours de traitement
            if ($newStatus === 'IN_PROGRESS') {
                $manager = $this->userRepository->findOneBy(['role' => 'MANAGER']);
                if ($manager) {
                    $this->notificationService->notifyTicketInProgress($ticket, $manager);
                }
            }
            
            // Notification spécifique si résolu
            if ($newStatus === 'RESOLVED') {
                $this->notificationService->notifyTicketResolved($ticket);
            }
            
            // Notification spécifique si fermé
            if ($newStatus === 'CLOSED') {
                $this->notificationService->notifyTicketClosed($ticket);
            }
        }


        return $this->json([
            'success' => true,
            'message' => 'Ticket status updated successfully',
            'data' => [
                'id' => $ticket->getId(),
                'title' => $ticket->getTitle(),
                'status' => $ticket->getStatus(),
                'old_status' => $oldStatus,
                'updatedAt' => $ticket->getUpdatedAt()->format('c'),
                'closedAt' => $ticket->getClosedAt()?->format('c')
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Met à jour un ticket existant
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $ticket = $this->ticketRepository->find($id);

        if (!$ticket) {
            return $this->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Mettre à jour les champs si fournis
        if (isset($data['title'])) {
            $ticket->setTitle($data['title']);
        }

        if (isset($data['description'])) {
            $ticket->setDescription($data['description']);
        }

        if (isset($data['priority']) && in_array($data['priority'], ['LOW', 'MEDIUM', 'HIGH'])) {
            $ticket->setPriority($data['priority']);
        }

        if (isset($data['status']) && in_array($data['status'], ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'])) {
            $ticket->setStatus($data['status']);
        }

        // Réassigner à un agent si demandé
        if (isset($data['reassign']) && $data['reassign'] === true) {
            $assignedAgent = $this->assignmentService->assignTicket($ticket);
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Ticket updated successfully',
            'data' => $ticket
        ], Response::HTTP_OK, [], [
            'groups' => ['ticket:read']
        ]);
    }

    /**
     * Supprime un ticket
     * 
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $ticket = $this->ticketRepository->find($id);

        if (!$ticket) {
            return $this->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($ticket);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Ticket deleted successfully'
        ], Response::HTTP_OK);
    }
}
