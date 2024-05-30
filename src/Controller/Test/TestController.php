<?php

// src/Controller/TestDataController.php

namespace App\Controller\Test;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\JobOffer;
use App\Entity\OfferView;



class TestController extends AbstractController
{
    #[Route('/generate-test-data',  name: "generate_test_data")]
    public function generateTestData(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les utilisateurs et toutes les offres d'emploi
        $users = $entityManager->getRepository(User::class)->findBy(['userType' => 'candidate']);
        $jobOffers = $entityManager->getRepository(JobOffer::class)->findAll();

        // Définir le nombre total d'interactions à créer
        $totalInteractions = 1000; // Par exemple, 1000 interactions aléatoires

        for ($i = 0; $i < $totalInteractions; $i++) {
            // Sélectionner aléatoirement un utilisateur et une offre d'emploi
            $randomUser = $users[array_rand($users)];
            $randomJobOffer = $jobOffers[array_rand($jobOffers)];

            // Rechercher une interaction existante pour cette combinaison d'utilisateur et d'offre d'emploi
            $existingView = $entityManager->getRepository(OfferView::class)->findOneBy([
                'user' => $randomUser,
                'jobOffer' => $randomJobOffer
            ]);

            if ($existingView) {
                // Si l'interaction existe, incrémentez le compteur de vues
                $existingView->incrementViewCount();
            } else {
                // Sinon, créez une nouvelle interaction avec un compteur de vues initialisé
                $newView = new OfferView();
                $newView->setUser($randomUser);
                $newView->setJobOffer($randomJobOffer);
                $newView->setViewCount(rand(0,100)); // Commencez avec 1 vue
                $entityManager->persist($newView);
            }

            // Pour améliorer les performances, vous pouvez appeler flush() après chaque x itérations ou à la fin de la boucle
            if (($i + 1) % 100 === 0) { // Par exemple, flush après chaque 100 itérations
                $entityManager->flush();
            }
        }

        $entityManager->flush(); // Assurez-vous de flusher les données restantes à la fin

        return new Response('Données de test générées avec succès.');
    }
}
