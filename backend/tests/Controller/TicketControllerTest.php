<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests fonctionnels pour le TicketController
 * Teste tous les endpoints REST de l'API tickets
 * 
 * Fixtures utilisées (AppFixtures.php) :
 * - Marie Dubois (id:1, CLIENT) - 4 tickets créés
 * - Jean Martin (id:2, CLIENT) - 4 tickets créés  
 * - Sophie Bernard (id:3, AGENT) - tickets assignés
 * - Pierre Dupont (id:4, AGENT) - tickets assignés
 * - Thomas Petit (id:5, MANAGER) - voit tous les tickets
 * 
 * NOTE: Les fixtures doivent être chargées avant d'exécuter les tests :
 * docker exec tickets_app php bin/console doctrine:fixtures:load --env=test --no-interaction
 */
class TicketControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test GET /api/tickets - Liste tous les tickets (MANAGER)
     * Teste également le temps de réponse
     */
    public function testListTicketsAsManager(): void
    {
        $startTime = microtime(true);
        
        $this->client->request('GET', '/api/tickets', ['user_id' => 5]); // Thomas Manager

        $executionTime = (microtime(true) - $startTime) * 1000; // en ms

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('user_role', $data);
        $this->assertEquals('MANAGER', $data['user_role']);
        $this->assertIsArray($data['data']);
        $this->assertGreaterThan(0, $data['count']);
        
        // Performance : doit répondre en moins de 15000ms (premier test = cache warming accepté)
        $this->assertLessThan(15000, $executionTime, 
            sprintf('List endpoint too slow: %.2fms (should be < 15000ms)', $executionTime));
        
        echo sprintf("\n✓ List tickets (MANAGER): %.2fms\n", $executionTime);
    }

    /**
     * Test GET /api/tickets - Liste des tickets CLIENT
     * CLIENT ne voit que ses propres tickets
     */
    public function testListTicketsAsClient(): void
    {
        $startTime = microtime(true);
        
        $this->client->request('GET', '/api/tickets', ['user_id' => 1]); // Marie Client

        $executionTime = (microtime(true) - $startTime) * 1000;

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($data['success']);
        $this->assertEquals('CLIENT', $data['user_role']);
        
        // Vérifier que tous les tickets appartiennent au client
        foreach ($data['data'] as $ticket) {
            $this->assertEquals(1, $ticket['creator']['id']);
        }
        
        $this->assertLessThan(500, $executionTime, 
            sprintf('List endpoint too slow: %.2fms', $executionTime));
        
        echo sprintf("\n✓ List tickets (CLIENT): %.2fms - %d tickets\n", $executionTime, $data['count']);
    }

    /**
     * Test GET /api/tickets - Liste des tickets AGENT
     * AGENT ne voit que les tickets qui lui sont assignés
     */
    public function testListTicketsAsAgent(): void
    {
        $startTime = microtime(true);
        
        $this->client->request('GET', '/api/tickets', ['user_id' => 3]); // Sophie Agent

        $executionTime = (microtime(true) - $startTime) * 1000;

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($data['success']);
        $this->assertEquals('AGENT', $data['user_role']);
        
        // Vérifier que tous les tickets sont assignés à cet agent
        foreach ($data['data'] as $ticket) {
            $this->assertNotNull($ticket['assignee']);
            $this->assertEquals(3, $ticket['assignee']['id']);
        }
        
        $this->assertLessThan(1500, $executionTime, 
            sprintf('List endpoint too slow: %.2fms', $executionTime));
        
        echo sprintf("\n✓ List tickets (AGENT): %.2fms - %d tickets\n", $executionTime, $data['count']);
    }

    /**
     * Test GET /api/tickets - Sans authentification (doit échouer)
     */
    public function testListTicketsWithoutAuth(): void
    {
        $this->client->request('GET', '/api/tickets');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Authentication required', $data['message']);
    }

    /**
     * Test GET /api/tickets/{id} - Récupère un ticket existant
     */
    public function testShowTicketExists(): void
    {
        // ID du premier ticket créé dans les fixtures
        $this->client->request('GET', '/api/tickets/1');

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals(1, $data['data']['id']);
        $this->assertArrayHasKey('title', $data['data']);
        $this->assertArrayHasKey('description', $data['data']);
        $this->assertArrayHasKey('status', $data['data']);
        $this->assertArrayHasKey('priority', $data['data']);
        $this->assertArrayHasKey('creator', $data['data']);
    }

    /**
     * Test GET /api/tickets/{id} - Ticket inexistant (404)
     */
    public function testShowTicketNotFound(): void
    {
        $this->client->request('GET', '/api/tickets/99999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Ticket not found', $data['message']);
    }

    /**
     * Test POST /api/tickets - Création avec succès
     */
    public function testCreateTicketSuccess(): void
    {
        $ticketData = [
            'title' => 'Test Ticket',
            'description' => 'Description du test',
            'priority' => 'HIGH',
            'creator_id' => 1  // Marie Client depuis les fixtures
        ];

        $this->client->request(
            'POST',
            '/api/tickets?user_id=1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($ticketData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertEquals('Ticket created and assigned successfully', $data['message']);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('Test Ticket', $data['data']['title']);
        $this->assertEquals('HIGH', $data['data']['priority']);
        $this->assertEquals('OPEN', $data['data']['status']); // Reste OPEN même après auto-assignation
        
        // Vérifier l'assignation automatique
        $this->assertArrayHasKey('assignee', $data['data']);
        $this->assertNotNull($data['data']['assignee']);
        $this->assertIsArray($data['data']['assignee']);
        $this->assertArrayHasKey('id', $data['data']['assignee']);
        $this->assertArrayHasKey('name', $data['data']['assignee']);
    }

    /**
     * Test POST /api/tickets - Titre manquant (400)
     */
    public function testCreateTicketMissingTitle(): void
    {
        $ticketData = [
            'description' => 'Description sans titre',
            'creator_id' => 1
        ];

        $this->client->request(
            'POST',
            '/api/tickets?user_id=1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($ticketData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Title is required and cannot be empty', $data['message']);
    }

    /**
     * Test POST /api/tickets - Description manquante (400)
     */
    public function testCreateTicketMissingDescription(): void
    {
        $ticketData = [
            'title' => 'Titre sans description',
            'creator_id' => 1
        ];

        $this->client->request(
            'POST',
            '/api/tickets?user_id=1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($ticketData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Description is required and cannot be empty', $data['message']);
    }

    /**
     * Test POST /api/tickets - Creator inexistant (404)
     */
    public function testCreateTicketCreatorNotFound(): void
    {
        $ticketData = [
            'title' => 'Test Ticket',
            'description' => 'Description',
            'creator_id' => 1  // ID valide pour l'authentification
        ];

        $this->client->request(
            'POST',
            '/api/tickets?user_id=99999',  // User ID inexistant
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($ticketData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('User not found', $data['message']);
    }

    /**
     * Test PUT /api/tickets/{id} - Modification avec succès
     */
    public function testUpdateTicketSuccess(): void
    {
        $updateData = [
            'title' => 'Titre modifié',
            'status' => 'IN_PROGRESS',
            'priority' => 'LOW'
        ];

        $this->client->request(
            'PUT',
            '/api/tickets/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertEquals('Ticket updated successfully', $data['message']);
        $this->assertEquals('Titre modifié', $data['data']['title']);
        $this->assertEquals('IN_PROGRESS', $data['data']['status']);
        $this->assertEquals('LOW', $data['data']['priority']);
    }

    /**
     * Test PUT /api/tickets/{id} - Ticket inexistant (404)
     */
    public function testUpdateTicketNotFound(): void
    {
        $updateData = [
            'title' => 'Titre modifié'
        ];

        $this->client->request(
            'PUT',
            '/api/tickets/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Ticket not found', $data['message']);
    }

    /**
     * Test DELETE /api/tickets/{id} - Suppression avec succès
     */
    public function testDeleteTicketSuccess(): void
    {
        // Utiliser un ticket des fixtures qui n'est pas modifié par d'autres tests (ID 7)
        $ticketId = 7;

        // Supprimer le ticket
        $this->client->request('DELETE', "/api/tickets/{$ticketId}");

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertEquals('Ticket deleted successfully', $data['message']);

        // Vérifier que le ticket n'existe plus
        $this->client->request('GET', "/api/tickets/{$ticketId}");
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Test DELETE /api/tickets/{id} - Ticket inexistant (404)
     */
    public function testDeleteTicketNotFound(): void
    {
        $this->client->request('DELETE', '/api/tickets/99999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Ticket not found', $data['message']);
    }

    /**
     * Test GET /api/tickets/stats - Statistiques avec performance (MANAGER)
     */
    public function testGetStatsAsManager(): void
    {
        $startTime = microtime(true);
        
        $this->client->request('GET', '/api/tickets/stats', ['user_id' => 5]); // Thomas Manager

        $executionTime = (microtime(true) - $startTime) * 1000;

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertEquals('MANAGER', $data['user_role']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data['data']);
        $this->assertArrayHasKey('by_status', $data['data']);
        $this->assertArrayHasKey('by_priority', $data['data']);
        
        $this->assertArrayHasKey('open', $data['data']['by_status']);
        $this->assertArrayHasKey('in_progress', $data['data']['by_status']);
        $this->assertArrayHasKey('resolved', $data['data']['by_status']);
        $this->assertArrayHasKey('closed', $data['data']['by_status']);
        
        $this->assertArrayHasKey('high', $data['data']['by_priority']);
        $this->assertArrayHasKey('medium', $data['data']['by_priority']);
        $this->assertArrayHasKey('low', $data['data']['by_priority']);
        
        // Performance : doit répondre en moins de 300ms
        $this->assertLessThan(300, $executionTime, 
            sprintf('Stats endpoint too slow: %.2fms (should be < 300ms)', $executionTime));
        
        echo sprintf("\n✓ Stats (MANAGER): %.2fms - Total: %d tickets\n", 
            $executionTime, $data['data']['total']);
    }

    /**
     * Test GET /api/tickets/stats - Statistiques CLIENT
     */
    public function testGetStatsAsClient(): void
    {
        $startTime = microtime(true);
        
        $this->client->request('GET', '/api/tickets/stats', ['user_id' => 1]); // Marie Client

        $executionTime = (microtime(true) - $startTime) * 1000;

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertEquals('CLIENT', $data['user_role']);
        
        $this->assertLessThan(300, $executionTime, 
            sprintf('Stats endpoint too slow: %.2fms', $executionTime));
        
        echo sprintf("\n✓ Stats (CLIENT): %.2fms - Total: %d tickets\n", 
            $executionTime, $data['data']['total']);
    }

    /**
     * Test GET /api/tickets/stats - Sans authentification (doit échouer)
     */
    public function testGetStatsWithoutAuth(): void
    {
        $this->client->request('GET', '/api/tickets/stats');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Authentication required', $data['message']);
    }
}
