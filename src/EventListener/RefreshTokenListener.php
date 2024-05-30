<?php

namespace App\EventListener;

use App\Entity\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class RefreshTokenListener
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $refreshToken = $this->generateRefreshToken($user);

        $data['refresh_token'] = $refreshToken;
        $expirationDate = $this->getRefreshTokenExpiration($refreshToken);

        $this->saveRefreshToken($refreshToken, $user->getEmail());

        $event->setData($data);
    }

    private function generateRefreshToken(UserInterface $user)
    {
        return bin2hex(random_bytes(32));
    }

    private function saveRefreshToken($refreshToken, $username)
    {
        $refreshTokenEntity = new RefreshToken($refreshToken, $username);

        $this->entityManager->persist($refreshTokenEntity);
        $this->entityManager->flush();
    }

    private function getRefreshTokenExpiration($refreshToken)
    {
        $refreshTokenEntity = $this->entityManager
            ->getRepository(RefreshToken::class)
            ->findOneBy(['refresh_token' => $refreshToken]);

        if ($refreshTokenEntity) {
            return $refreshTokenEntity->getValid();
        }

        return new \DateTime('+30 days');
    }
}
