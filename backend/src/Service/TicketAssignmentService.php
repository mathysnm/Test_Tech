<?php

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service d'assignation automatique des tickets
 * 
 * Logique :
 * - Trouve l'agent (rôle AGENT uniquement) avec le moins de tickets actifs (OPEN + IN_PROGRESS)
 * - Le MANAGER est exclu de l'assignation automatique (il reçoit seulement des notifications)
 * - Si plusieurs agents ont le même nombre, prend le premier par ordre d'ID
 * - Si aucun agent n'est disponible, retourne null
 * - Compte les tickets actifs : OPEN (nouvellement assignés) + IN_PROGRESS (en traitement)
 */
class TicketAssignmentService
{
    public function __construct(
        private UserRepository $userRepository,
        private TicketRepository $ticketRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Assigne automatiquement un ticket à l'agent le moins chargé
     * 
     * @param Ticket $ticket Le ticket à assigner
     * @return User|null L'agent assigné, ou null si aucun agent disponible
     */
    public function assignTicket(Ticket $ticket): ?User
    {
        // Récupérer tous les agents (AGENT uniquement, pas MANAGER)
        $agents = $this->userRepository->createQueryBuilder('u')
            ->where('u.role = :role')
            ->setParameter('role', 'AGENT')
            ->getQuery()
            ->getResult();

        if (empty($agents)) {
            return null; // Aucun agent disponible
        }

        // Compter les tickets actifs (OPEN + IN_PROGRESS) pour chaque agent
        $agentWorkload = [];
        foreach ($agents as $agent) {
            $count = $this->ticketRepository->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->where('t.assignee = :agent')
                ->andWhere('t.status IN (:statuses)')
                ->setParameter('agent', $agent)
                ->setParameter('statuses', ['OPEN', 'IN_PROGRESS'])
                ->getQuery()
                ->getSingleScalarResult();
            
            $agentWorkload[$agent->getId()] = [
                'agent' => $agent,
                'workload' => $count
            ];
        }

        // Trier par charge de travail (workload ASC), puis par ID pour être déterministe
        uasort($agentWorkload, function($a, $b) {
            $workloadDiff = $a['workload'] <=> $b['workload'];
            if ($workloadDiff !== 0) {
                return $workloadDiff;
            }
            // En cas d'égalité, trier par ID pour une distribution équitable
            return $a['agent']->getId() <=> $b['agent']->getId();
        });

        // Prendre le premier agent (le moins chargé)
        $selectedAgent = reset($agentWorkload)['agent'];

        // Assigner le ticket
        $ticket->setAssignee($selectedAgent);
        $this->entityManager->flush();

        return $selectedAgent;
    }

    /**
     * Récupère la charge de travail de tous les agents
     * 
     * @return array<int, array{agent: User, workload: int}>
     */
    public function getAgentsWorkload(): array
    {
        $agents = $this->userRepository->createQueryBuilder('u')
            ->where('u.role = :role')
            ->setParameter('role', 'AGENT')
            ->getQuery()
            ->getResult();

        $workload = [];
        foreach ($agents as $agent) {
            $count = $this->ticketRepository->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->where('t.assignee = :agent')
                ->andWhere('t.status IN (:statuses)')
                ->setParameter('agent', $agent)
                ->setParameter('statuses', ['OPEN', 'IN_PROGRESS'])
                ->getQuery()
                ->getSingleScalarResult();
            
            $workload[] = [
                'agent' => $agent,
                'workload' => (int) $count
            ];
        }

        // Trier par workload ASC
        usort($workload, fn($a, $b) => $a['workload'] <=> $b['workload']);

        return $workload;
    }

    /**
     * Vérifie si un agent est disponible
     * 
     * @return bool
     */
    public function hasAvailableAgent(): bool
    {
        $count = $this->userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', 'AGENT')
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
