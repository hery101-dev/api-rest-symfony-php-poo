<?php

namespace App\Controller\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api')]
class UserController extends AbstractController
{
  #[Route('/user', name: 'api_me', methods: ['GET'])]
  public function apiGetUser(
    EntityManagerInterface $entityManager
  ): Response {
    $user = $this->getUser();
    if (!$user) {
      return $this->json('Aucun utilisateur trouvé', 404);
    }
    $userBD = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getUserIdentifier()]);
    if (!$userBD) {
      return $this->json('Aucune correspondance pour l\'email');
    }
    return $this->json([
      'id' => $userBD->getId(),
      'email' => $user->getUserIdentifier(),
      'username' => $userBD->getUsername(),
      'roles' => $userBD->getUserType(),
      'enabledRecommandation' => $userBD->isRecommendationsEnabled(),
    ]);
  }


  #[Route('/user/edit', name: 'app_user_edit', methods: ['PUT', 'PATCH'])]
  public function edit(
    Request $request,
    User $user,
    EntityManagerInterface $entityManager,
    JWTTokenManagerInterface $jwtManager
  ): JsonResponse {
    $user = $this->getUser();
    if (!$user) {
      return $this->json('Aucun utilisateur trouvé', 404);
    }
    $findUser = $entityManager->getRepository(User::class)->find($user);
    if (!$findUser) {
      return $this->json('Aucun utilisateur correspondant');
    }

    $content = json_decode($request->getContent(), true);
    if (!$content) {
      return $this->json('Données manquantes');
    }

    $findUser->setEmail($content['email']);
    try {
      $entityManager->flush();
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], 500);
    }
    $token = $jwtManager->create($user);

    $data = [
      'email' => $findUser->getEmail(),
      'password' => $findUser->getPassword(),
      'token' => $token,
    ];

    return $this->json($data);
  }
}
