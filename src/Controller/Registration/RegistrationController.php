<?php

namespace App\Controller\Registration;

use App\Entity\User;
use App\Service\MailerService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerService $mailerService,
        TokenGeneratorInterface $tokenGeneratorInterface,
        HttpClientInterface $httpClient
    ): Response {

        $user = new User();
        $content = json_decode($request->getContent(), true);
        if (!$content) {
            return $this->json('Données manquantes requises');
        }
       
        $username = $content['username'];
        $email = $content['email'];
        $password = $content['password'];
        $userType = $content['userType'];

        $tokenRegistration = $tokenGeneratorInterface->generateToken();

        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );
        $user->setUsername($username);
        $user->setEmail($email);
        if ($userType === 'candidate') {
            $user->setUserType((string)$userType);
            $user->setRoles(["ROLE_CANDIDATE"]);
        } else {
            return $this->json('error of user type', 404);
        }

        $user->setToken($tokenRegistration);

        $entityManager->persist($user);
        $entityManager->flush();

        $mailerService->send(
            $user->getEmail(),
            'Confirming account user',
            'registration_confirmation.html.twig',
            [
                'user' => $user,
                'token' => $tokenRegistration,
                'lifeTimeToken' => $user->getTokenLifeTime()->format('d-M-Y H:i:s')
            ]
        );

        return $this->json([
            'success' => true,
            'token' => $tokenRegistration,
            'message' => 'Your account has been created successfully, please check your emails to activate it.',
        ], 200);
    }



    private function verifyRecaptcha(string $recaptchaResponse, HttpClientInterface $httpClient): bool
    {
        $response = $httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => '6LeNc4cpAAAAAF87D-TX7d7IJx4dw0JDNATJaS3l',
                'response' => $recaptchaResponse,
            ]
        ]);

        $responseData = $response->toArray();

        return $responseData['success'] ?? false;
    }



    #[Route('/verify-account', name: 'account_verify', methods: ['POST'])]
    public function verify(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['token']) || !isset($data['id'])) {
            return $this->json(['error' => 'Necessary settings are missing.'], 400);
        }

        $token = $data['token'];
        $id = $data['id'];

        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);

        if ($user->getToken() !== $token) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized access, please check your email'], 401);
        }

        if ($user->getToken() === null) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized access, please check your email'], 401);
        }

        if (new DateTime('now') > $user->getTokenLifeTime()) {
            return new JsonResponse(['success' => false, 'message' => 'Your confirmation date has expired. Please re-register.'], 403);
        }

        $user->setIsVerified(true);
        $user->setToken(null);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'isEmailVerified' => $user->isIsVerified(),
            'message' => 'Your account is confirmed, you can now log in'
        ], 200);
    }
}
