<?php

namespace App\Controller\TypeContrat;

use App\Entity\Contrat;
use App\Entity\JobOffer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ContratListController extends AbstractController
{
    #[Route('/contrat-list', name: 'contrat_list', methods: ['GET'])]
    public function indexContrat(EntityManagerInterface $entityManager): JsonResponse
    {
        $contrats = $entityManager->getRepository(Contrat::class)->findAll();

        $dataContrat = [];

        foreach ($contrats as $contrat) {
            $dataContrat[] = [
                'id' => $contrat->getId(),
                'type' => $contrat-> getType()
            ];
        }
        return $this->json($dataContrat);
    }

}