<?php

namespace App\Controller\ForgotPassword;

use DateTime;
use App\Entity\User;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ForgotPasswordController extends AbstractController
{
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    #[Route('/forgot-password', name: 'forgot_password_user', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        TokenGeneratorInterface $tokenGeneratorInterface,
        ResetPasswordService $resetPasswordService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return $this->json(['message' => 'Email is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $data['email']]);
        $tokenRegistration = $tokenGeneratorInterface->generateToken();

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $user->setToken($tokenRegistration);
        $user->setIsVerified(false);
        //$user->setTokenLifeTime(new \DateTime('+1 days'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $resetPasswordService->send(
            $user->getEmail(),
            'Reset password',
            'reset_password.html.twig',
            [
                'user' => $user,
                'token' => $tokenRegistration,
                'lifeTimeToken' => $user->getTokenLifeTime()->format('d-M-Y H:i:s')
            ]
        );

        return $this->json([
            'success' => true,
            'token' => $tokenRegistration,
            'message' => 'Please check your emails to reset password.',
        ], 200);
    }

    
    #[Route('/reset-password', name: 'reset_password_user', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['token']) || !isset($data['id']) || !isset($data['password'])) {
            return $this->json(['error' => 'Necessary settings are missing.'], 400);
        }

        $token = $data['token'];
        $id = $data['id'];
        $password = $data['password'];

        $user = $this->userRepository->findOneBy(['id' => $id]);

        if ($user->getToken() !== $token) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized access, please check your email'], 401);
        }

        if ($user->getToken() === null) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized access, please check your email'], 401);
        }

        if (new DateTime('now') > $user->getTokenLifeTime()) {
            return new JsonResponse(['success' => false, 'message' => 'Your confirmation date has expired. Please re-register.'], 403);
        }
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );
        $user->setIsVerified(true);
        $user->setToken(null);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'isEmailVerified' => $user->isIsVerified(),
            'message' => 'Your password has been changed, you can now log in'
        ], 200);
    }
}
