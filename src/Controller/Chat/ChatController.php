<?php

/*
namespace App\Controller\Chat;

use App\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
   
    public function storeMessage(Request $request): Response
    {
        // Récupérer les données du message depuis la requête
        $data = json_decode($request->getContent(), true);

        // Créer une nouvelle instance de l'entité Message
        $message = new Message();
        $message->setText($data['text']);
        $message->setSenderId($data['senderId']);
        // Ajoutez d'autres données au besoin, comme la date et l'heure du message

        // Persistez le message dans la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($message);
        $entityManager->flush();

        // Répondre avec une confirmation
        return new Response('Message stored successfully', Response::HTTP_CREATED);
    }
}
*/