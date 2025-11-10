<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur des Notifications
 * Gère la récupération et le marquage des notifications utilisateur
 */
#[Route('/api/notifications', name: 'api_notifications_')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Récupère les notifications d'un utilisateur
     * Authentification OBLIGATOIRE - user_id requis en query parameter
     * 
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // Authentification OBLIGATOIRE
        $userId = $request->query->get('user_id');
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

        // Paramètres de filtrage optionnels
        $unreadOnly = $request->query->get('unread_only', 'false') === 'true';
        $limit = (int) $request->query->get('limit', 50);

        // Récupérer les notifications
        $criteria = ['user' => $user];
        if ($unreadOnly) {
            $criteria['isRead'] = false;
        }

        $notifications = $this->notificationRepository->findBy(
            $criteria,
            ['createdAt' => 'DESC'],
            $limit
        );

        // Compter les non lues
        $unreadCount = $this->notificationRepository->count([
            'user' => $user,
            'isRead' => false
        ]);

        // Formater les notifications
        $notificationsData = array_map(function ($notification) {
            return [
                'id' => $notification->getId(),
                'type' => $notification->getType(),
                'message' => $notification->getMessage(),
                'isRead' => $notification->isRead(),
                'createdAt' => $notification->getCreatedAt()->format('c')
            ];
        }, $notifications);

        return $this->json([
            'success' => true,
            'data' => $notificationsData,
            'unreadCount' => $unreadCount,
            'total' => count($notificationsData)
        ]);
    }

    /**
     * Marque une notification comme lue
     * Authentification OBLIGATOIRE - user_id requis dans le body
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/{id}/read', name: 'mark_read', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function markAsRead(int $id, Request $request): JsonResponse
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

        $notification = $this->notificationRepository->find($id);
        if (!$notification) {
            return $this->json([
                'success' => false,
                'message' => 'Notification not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que la notification appartient à l'utilisateur
        if ($notification->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'You can only mark your own notifications as read'
            ], Response::HTTP_FORBIDDEN);
        }

        // Marquer comme lue
        $notification->setRead(true);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'id' => $notification->getId(),
                'isRead' => $notification->isRead()
            ]
        ]);
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     * Authentification OBLIGATOIRE - user_id requis dans le body
     * 
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/read-all', name: 'mark_all_read', methods: ['POST'])]
    public function markAllAsRead(Request $request): JsonResponse
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

        // Récupérer toutes les notifications non lues
        $unreadNotifications = $this->notificationRepository->findBy([
            'user' => $user,
            'isRead' => false
        ]);

        $count = count($unreadNotifications);

        // Marquer toutes comme lues
        foreach ($unreadNotifications as $notification) {
            $notification->setRead(true);
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => sprintf('%d notification(s) marked as read', $count),
            'data' => [
                'markedCount' => $count
            ]
        ]);
    }
}
