<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * Liste tous les utilisateurs disponibles pour la connexion
     * Endpoint public pour permettre la sélection d'un utilisateur
     * 
     * @return JsonResponse
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Récupérer tous les utilisateurs
        $users = $this->userRepository->findAll();

        // Construire la réponse avec les données essentielles
        $usersData = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole()
            ];
        }, $users);

        return $this->json([
            'success' => true,
            'count' => count($usersData),
            'data' => $usersData
        ], Response::HTTP_OK);
    }

    /**
     * Récupère un utilisateur par son ID
     * 
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole()
            ]
        ], Response::HTTP_OK);
    }
}
