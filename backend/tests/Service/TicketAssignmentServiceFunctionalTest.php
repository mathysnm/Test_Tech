<?php

namespace App\Tests\Service;

use App\Entity\Ticket;
use App\Entity\User;
use App\Service\TicketAssignmentService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests fonctionnels pour TicketAssignmentService
 * Utilise une vraie base de données de test
 */
class TicketAssignmentServiceFunctionalTest extends KernelTestCase
{
    private TicketAssignmentService $service;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = static::getContainer()->get(TicketAssignmentService::class);
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        // Nettoyer la BDD avant chaque test
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    /**
     * Test assignation avec agents disponibles
     */
    public function testAssignTicketWithAvailableAgents(): void
    {
        // Créer des agents
        $agent1 = $this->createAgent('Agent 1', 'agent1@test.com');
        $agent2 = $this->createAgent('Agent 2', 'agent2@test.com');

        // Agent 1 a déjà 2 tickets IN_PROGRESS
        $this->createAssignedTicket($agent1, 'IN_PROGRESS');
        $this->createAssignedTicket($agent1, 'IN_PROGRESS');

        // Agent 2 a déjà 1 ticket IN_PROGRESS
        $this->createAssignedTicket($agent2, 'IN_PROGRESS');

        // Créer un client
        $client = $this->createClient('Client Test', 'client@test.com');

        // Créer un nouveau ticket à assigner
        $ticket = new Ticket();
        $ticket->setTitle('Nouveau ticket');
        $ticket->setDescription('Test description');
        $ticket->setPriority('MEDIUM');
        $ticket->setStatus('OPEN');
        $ticket->setCreator($client);

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        // Exécuter l'assignation
        $assignedAgent = $this->service->assignTicket($ticket);

        // Vérifier que l'agent 2 (le moins chargé) est assigné
        $this->assertNotNull($assignedAgent);
        $this->assertEquals('Agent 2', $assignedAgent->getName());
        $this->assertSame($assignedAgent, $ticket->getAssignee());
    }

    /**
     * Test assignation quand aucun agent n'existe
     */
    public function testAssignTicketWithNoAgents(): void
    {
        // Créer seulement un client
        $client = $this->createClient('Client Test', 'client@test.com');

        // Créer un ticket
        $ticket = new Ticket();
        $ticket->setTitle('Ticket sans agent');
        $ticket->setDescription('Test');
        $ticket->setPriority('HIGH');
        $ticket->setStatus('OPEN');
        $ticket->setCreator($client);

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        // Exécuter l'assignation
        $assignedAgent = $this->service->assignTicket($ticket);

        // Vérifier qu'aucun agent n'est assigné
        $this->assertNull($assignedAgent);
        $this->assertNull($ticket->getAssignee());
    }

    /**
     * Test récupération de la charge de travail
     */
    public function testGetAgentsWorkload(): void
    {
        // Créer des agents
        $agent1 = $this->createAgent('Agent 1', 'agent1@test.com');
        $agent2 = $this->createAgent('Agent 2', 'agent2@test.com');

        // Agent 1 a 5 tickets IN_PROGRESS
        for ($i = 0; $i < 5; $i++) {
            $this->createAssignedTicket($agent1, 'IN_PROGRESS');
        }

        // Agent 2 a 2 tickets IN_PROGRESS
        for ($i = 0; $i < 2; $i++) {
            $this->createAssignedTicket($agent2, 'IN_PROGRESS');
        }

        // Agent 1 a aussi 3 tickets CLOSED (ne doivent pas être comptés)
        for ($i = 0; $i < 3; $i++) {
            $this->createAssignedTicket($agent1, 'CLOSED');
        }

        // Récupérer la charge de travail
        $workload = $this->service->getAgentsWorkload();

        // Vérifier
        $this->assertCount(2, $workload);
        $this->assertEquals('Agent 2', $workload[0]['agent']->getName());
        $this->assertEquals(2, $workload[0]['workload']);
        $this->assertEquals('Agent 1', $workload[1]['agent']->getName());
        $this->assertEquals(5, $workload[1]['workload']);
    }

    /**
     * Test hasAvailableAgent
     */
    public function testHasAvailableAgent(): void
    {
        // Sans agents
        $this->assertFalse($this->service->hasAvailableAgent());

        // Avec un agent
        $this->createAgent('Agent Test', 'agent@test.com');
        $this->assertTrue($this->service->hasAvailableAgent());
    }

    /**
     * Test assignation avec égalité de workload
     * Quand plusieurs agents ont le même nombre de tickets, 
     * l'assignation doit être déterministe (par ID croissant)
     */
    public function testAssignTicketWithEqualWorkload(): void
    {
        // Créer 3 agents avec le même workload (0 tickets)
        $agent1 = $this->createAgent('Agent 1', 'agent1@test.com');
        $agent2 = $this->createAgent('Agent 2', 'agent2@test.com');
        $agent3 = $this->createAgent('Agent 3', 'agent3@test.com');

        // Créer un client
        $client = $this->createClient('Client Test', 'client@test.com');

        // Créer un ticket à assigner
        $ticket = new Ticket();
        $ticket->setTitle('Ticket avec égalité');
        $ticket->setDescription('Test égalité');
        $ticket->setPriority('MEDIUM');
        $ticket->setStatus('OPEN');
        $ticket->setCreator($client);

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        // Assigner le ticket
        $assignedAgent = $this->service->assignTicket($ticket);

        // Vérifier qu'un agent est assigné et que c'est celui avec le plus petit ID
        $this->assertNotNull($assignedAgent);
        $this->assertEquals($agent1->getId(), $assignedAgent->getId());
        $this->assertEquals('Agent 1', $assignedAgent->getName());
    }

    // === Helper methods ===

    private function cleanDatabase(): void
    {
        $conn = $this->entityManager->getConnection();
        $conn->executeStatement('TRUNCATE TABLE ticket_log RESTART IDENTITY CASCADE');
        $conn->executeStatement('TRUNCATE TABLE notification RESTART IDENTITY CASCADE');
        $conn->executeStatement('TRUNCATE TABLE ticket RESTART IDENTITY CASCADE');
        $conn->executeStatement('TRUNCATE TABLE "user" RESTART IDENTITY CASCADE');
    }

    private function createAgent(string $name, string $email): User
    {
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setRole('AGENT');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createClient(string $name, string $email): User
    {
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setRole('CLIENT');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createAssignedTicket(User $assignee, string $status): Ticket
    {
        // Créer un nouveau client à chaque fois (car la BDD est nettoyée entre les tests)
        $client = $this->createClient('Client Default ' . uniqid(), 'client_' . uniqid() . '@test.com');

        $ticket = new Ticket();
        $ticket->setTitle('Test Ticket ' . uniqid());
        $ticket->setDescription('Description');
        $ticket->setPriority('MEDIUM');
        $ticket->setStatus($status);
        $ticket->setCreator($client);
        $ticket->setAssignee($assignee);

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        return $ticket;
    }
}
