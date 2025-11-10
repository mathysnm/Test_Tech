<?php

namespace App\Controller;

use App\Repository\ApplicationLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/logs', name: 'api_logs_')]
class LogController extends AbstractController
{
    public function __construct(
        private ApplicationLogRepository $logRepository
    ) {
    }

    /**
     * Récupère les logs récents de l'application
     * Accessible uniquement par les MANAGERs
     * 
     * @return JsonResponse
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // Récupérer les paramètres
        $limit = $request->query->get('limit', 100);
        $action = $request->query->get('action'); // Filtre optionnel par type d'action

        // Récupérer les logs
        if ($action) {
            $logs = $this->logRepository->findByAction($action, $limit);
        } else {
            // Logs des dernières 24h par défaut
            $since = new \DateTime('-24 hours');
            $logs = $this->logRepository->findRecentLogs($limit, $since);
        }

        // Formater la réponse
        $logsData = array_map(function($log) {
            return [
                'id' => $log->getId(),
                'action' => $log->getAction(),
                'user' => $log->getUser() ? [
                    'id' => $log->getUser()->getId(),
                    'name' => $log->getUser()->getName(),
                    'role' => $log->getUser()->getRole()
                ] : null,
                'entity_type' => $log->getEntityType(),
                'entity_id' => $log->getEntityId(),
                'details' => $log->getDetails(),
                'ip_address' => $log->getIpAddress(),
                'created_at' => $log->getCreatedAt()->format('c')
            ];
        }, $logs);

        return $this->json([
            'success' => true,
            'count' => count($logsData),
            'data' => $logsData
        ], Response::HTTP_OK);
    }

    /**
     * Statistiques des actions (pour dashboard manager)
     * 
     * @return JsonResponse
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(Request $request): JsonResponse
    {
        // Récupérer les stats depuis une période
        $period = $request->query->get('period', 'today'); // today, week, month
        
        $since = match($period) {
            'today' => new \DateTime('today'),
            'week' => new \DateTime('-7 days'),
            'month' => new \DateTime('-30 days'),
            default => new \DateTime('today')
        };

        $actionCounts = $this->logRepository->countByAction($since);

        return $this->json([
            'success' => true,
            'period' => $period,
            'since' => $since->format('c'),
            'data' => $actionCounts
        ], Response::HTTP_OK);
    }

    /**
     * Récupère les dernières connexions
     * 
     * @return JsonResponse
     */
    #[Route('/logins', name: 'logins', methods: ['GET'])]
    public function logins(Request $request): JsonResponse
    {
        $limit = $request->query->get('limit', 20);
        $logins = $this->logRepository->findRecentLogins($limit);

        $loginsData = array_map(function($log) {
            return [
                'id' => $log->getId(),
                'user' => $log->getUser() ? [
                    'id' => $log->getUser()->getId(),
                    'name' => $log->getUser()->getName(),
                    'role' => $log->getUser()->getRole()
                ] : null,
                'ip_address' => $log->getIpAddress(),
                'user_agent' => $log->getUserAgent(),
                'created_at' => $log->getCreatedAt()->format('c')
            ];
        }, $logins);

        return $this->json([
            'success' => true,
            'count' => count($loginsData),
            'data' => $loginsData
        ], Response::HTTP_OK);
    }
}
