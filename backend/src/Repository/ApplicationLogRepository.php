<?php

namespace App\Repository;

use App\Entity\ApplicationLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApplicationLog>
 */
class ApplicationLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationLog::class);
    }

    /**
     * Récupère les logs récents (dernières 24h par défaut)
     */
    public function findRecentLogs(int $limit = 100, ?\DateTimeInterface $since = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($since) {
            $qb->andWhere('l.createdAt >= :since')
               ->setParameter('since', $since);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les logs d'un utilisateur spécifique
     */
    public function findByUser(int $userId, int $limit = 50): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les logs par type d'action
     */
    public function findByAction(string $action, int $limit = 50): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.action = :action')
            ->setParameter('action', $action)
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les actions par type (pour statistiques)
     */
    public function countByAction(\DateTimeInterface $since = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->select('l.action, COUNT(l.id) as count')
            ->groupBy('l.action')
            ->orderBy('count', 'DESC');

        if ($since) {
            $qb->andWhere('l.createdAt >= :since')
               ->setParameter('since', $since);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les dernières connexions
     */
    public function findRecentLogins(int $limit = 20): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.action = :action')
            ->setParameter('action', 'USER_LOGIN')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
