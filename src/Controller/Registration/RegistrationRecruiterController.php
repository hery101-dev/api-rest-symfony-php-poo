<?php

namespace App\Controller\Registration;

use DateTime;
use App\Entity\User;
use App\Entity\Company;
use App\Entity\Partner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ConfirmationMailRecruiterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationRecruiterController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/recruiter/register-and-confirm-email', name: 'register_recruiter_and_confirm_email', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        ConfirmationMailRecruiterService $mailerService,
        TokenGeneratorInterface $tokenGeneratorInterface,
    ): Response {


        $content = json_decode($request->getContent(), true);
        if (!$content) {
            return $this->json('Missing required data', Response::HTTP_BAD_REQUEST);
        }

        $email = $content['email'];

        $emailExist = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($emailExist) {
            return $this->json(['message' => 'Email is already in use'], Response::HTTP_CONFLICT);
        }

        $password = $content['password'];
        $userType = $content['userType'];
        //$acceptTerms = $content['acceptTerms'];
        $name = $content['name'];
        $firstName = $content['firstname'];
        $phoneNumber = $content['phoneNumber'];
       

        $tokenRegistration = $tokenGeneratorInterface->generateToken();
        try {
            $user = new User;
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );

            $user->setEmail($email);
            if ($userType === 'recruiter') {
                $user->setUserType((string)$userType);
                $user->setRoles(["ROLE_RECRUITER"]);
            } else {
                return $this->json('error of user type', 404);
            }

            $user->setToken($tokenRegistration);
            $this->entityManager->persist($user);

            $partner = new Partner;
            $partner->setName(strtoupper($name));
            $partner->setFirstname($firstName);
            $partner->setPhoneNumber($phoneNumber);
            $partner->setUser($user);
            $this->entityManager->persist($partner);

            $this->entityManager->flush();

            $mailerService->send(
                $user->getEmail(),
                'Confirmation of registration on The Recrut',
                'registration_confirmation_recruiter.html.twig',
                [
                    'user' => $user,
                    'partner' => $partner,
                    'token' => $tokenRegistration,
                    'lifeTimeToken' => $user->getTokenLifeTime()->format('d-M-Y H:i:s')
                ]
            );

            return $this->json([
                'success' => true,
                'token' => $tokenRegistration,
                'message' => 'Your account has been created successfully, please check your emails to activate it.',
            ],  Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
