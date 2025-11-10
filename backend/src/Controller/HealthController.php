<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur de santé de l'API
 * Permet de vérifier que le backend fonctionne et que la BDD est accessible
 */
class HealthController extends AbstractController
{
    #[Route('/api/health', name: 'api_health', methods: ['GET'])]
    public function health(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        TicketRepository $ticketRepository
    ): JsonResponse
    {
        try {
            // Test connexion BDD
            $em->getConnection()->connect();
            $dbConnected = $em->getConnection()->isConnected();

            // Statistiques rapides
            $userCount = $userRepository->count([]);
            $ticketCount = $ticketRepository->count([]);
            
            // Comptage par rôle
            $clientCount = $userRepository->count(['role' => 'CLIENT']);
            $agentCount = $userRepository->count(['role' => 'AGENT']);
            $managerCount = $userRepository->count(['role' => 'MANAGER']);

            return $this->json([
                'status' => 'OK',
                'message' => 'API is running',
                'timestamp' => new \DateTime(),
                'database' => [
                    'connected' => $dbConnected,
                    'statistics' => [
                        'users' => [
                            'total' => $userCount,
                            'clients' => $clientCount,
                            'agents' => $agentCount,
                            'managers' => $managerCount,
                        ],
                        'tickets' => $ticketCount,
                    ]
                ],
                'version' => '1.0.0-alpha',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'ERROR',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/api', name: 'api_root', methods: ['GET'])]
    public function root(): JsonResponse
    {
        return $this->json([
            'name' => 'Tickets Support API',
            'version' => '1.0.0-alpha',
            'endpoints' => [
                '/api/health' => 'Health check endpoint',
                '/api/users' => 'Users management (à venir)',
                '/api/tickets' => 'Tickets management (à venir)',
                '/api/notifications' => 'Notifications management (à venir)',
            ],
            'documentation' => '/api/doc (Swagger - à venir)',
        ]);
    }
}
