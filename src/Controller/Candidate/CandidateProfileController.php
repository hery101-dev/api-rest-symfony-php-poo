<?php

namespace App\Controller\Candidate;

use App\Entity\Location;
use App\Entity\Partner;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/api')]
#[IsGranted('ROLE_CANDIDATE')]
class CandidateProfileController extends AbstractController
{
    #[Route("/candidate/profile", name: "index_profile_candidate", methods: ["GET"])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('Utilisateur introuvable', 404);
        }

        $partnerBD = $entityManager->getRepository(Partner::class)->findOneBy(['user' => $userActuel]);
        if (!$partnerBD) {
            return $this->json('Aucun profil trouvé');
        }

        $data[] = [
            'id' => $partnerBD->getId() ? $partnerBD->getId() : null,
            'name' => $partnerBD->getName() ? $partnerBD->getName() :null,
            'firstName' => $partnerBD->getFirstname() ? $partnerBD->getFirstname() : null,
            'nationality' => $partnerBD->getNationality() ? $partnerBD->getNationality() : null,
            'phoneNumber' => $partnerBD->getPhoneNumber() ? $partnerBD->getPhoneNumber() : null,
            'job' => $partnerBD->getJob() ?  $partnerBD->getJob() : null
        ];
        return $this->json($data);
    }


    #[Route('/candidate/display/location', name: 'display_location', methods: ['GET'])]
    public function location(EntityManagerInterface $entityManager): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('Utilisateur introuvable', 404);
        }

        $locationBD = $entityManager->getRepository(Location::class)->findOneBy(['user' => $userActuel]);
        if (!$locationBD) {
            return $this->json('Aucun lieu trouvé');
        }

        $data[] = [
            'id' => $locationBD->getId() ? $locationBD->getId() : null,
            'city' => $locationBD->getCity() ? $locationBD->getCity() : null,
            'country' => $locationBD->getCountry() ? $locationBD->getCountry() : null,
            'zipCode' => $locationBD->getZipCode() ? $locationBD->getZipCode() : null,
            'address' => $locationBD->getAddress() ? $locationBD->getAddress() : null,
        ];
        return $this->json($data);
    }


    #[Route('/candidate/create/location', name: 'create_location_candidate', methods: ['POST'])]
    public function createLocation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('Utilisateur introuvable', 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json('Données requises manquantes');
        }

        $location = new Location();
        $location->setCity($data['city']);
        $location->setCountry($data['country']);
        $location->setZipCode($data['zipCode']);
        $location->setAddress($data['address']);
        $location->setUserId($userActuel);

        $entityManager->persist($location);
        $entityManager->flush();

        return $this->json('Lieu créé avec succès');
    }


    #[Route('/candidate/create/profile', name: 'create_profile_candidate', methods: ['POST'])]
    public function createProfile(
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {

        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('Utilisateur introuvable', 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json('Données requises manquantes');
        }

        $partner = new Partner();
        $partner->setName(strtoupper($data['name']));
        $partner->setFirstName($data['firstName']);
        $partner->setNationality($data['nationality']);
        $partner->setPhoneNumber($data['phoneNumber']);
        $partner->setJob($data['job']);    
        $partner->setUser($userActuel);

        $entityManager->persist($partner);
        $entityManager->flush();

        return $this->json('Nouveau profil créé avec succès');
    }



    #[Route("/candidate/edit/profile", name: "edit_profile_candidate", methods: ["PUT"])]
    public function editProfile(EntityManagerInterface $entityManager, Request $request): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('Aucun utilisateur trouvé', 404);
        }

        $partnerEdit = $entityManager->getRepository(Partner::class)->findOneBy(['user' => $userActuel]);
        if (!$partnerEdit) {
            return $this->json('Profil introuvable');
        }

        $content = json_decode($request->getContent(), true);
        if (!$content) {
            return $this->json('Données requises manquantes');
        }
        $partnerEdit->setName($content['name']);
        $partnerEdit->setFirstname($content['firstName']);
        //$partnerEdit->setGender($content['selectedGender']);
        $partnerEdit->setNationality($content['nationality']);
        $partnerEdit->setPhoneNumber($content['phoneNumber']);
        $partnerEdit->setJob($content['job']);

        $entityManager->flush();

        return $this->json('Modification des données avec succès');
    }


    #[Route("/candidate/edit/location", name: "edit_location_candidate", methods: ["PUT"])]
    public function editLocation(EntityManagerInterface $entityManager, Request $request): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('Aucun utilisateur trouvé', 404);
        }

        $locationEdit = $entityManager->getRepository(Location::class)->findOneBy(['user' => $userActuel]);
        if (!$locationEdit) {
            return $this->json('Location introuvable');
        }

        $content = json_decode($request->getContent(), true);
        if (!$content) {
            return $this->json('Données requises manquantes');
        }
       
        $locationEdit->setAddress($content['address']);
        $locationEdit->setCity($content['city']);
        $locationEdit->setCountry($content['country']);
        $locationEdit->setZipCode($content['zipCode']);

        $entityManager->flush();

        return $this->json('Modification des données avec succès');
    }
}
