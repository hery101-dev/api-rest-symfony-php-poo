<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class LoggedController extends AbstractController
{
    #[Route('/user/check-login', name: 'check_login')]
    public function checkLogin(Security $security): JsonResponse
    {
        $isLoggedIn = $security->isGranted('IS_AUTHENTICATED_REMEMBERED');

        return $this->json(['isLoggedIn' => $isLoggedIn]);
    }
}
