<?php

namespace App\Controller\Recruiter;

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
#[IsGranted('ROLE_RECRUITER')]
class ProfileRecruiterController extends AbstractController
{
    #[Route("/profile", name: "index_profile_recruiter", methods: ["GET"])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }

        $partnerBD = $entityManager->getRepository(Partner::class)->findOneBy(['user' => $userActuel]);
        $locationBD = $entityManager->getRepository(Location::class)->findOneBy(['user' => $userActuel]);

        $data[] = [
            'id' => $partnerBD ? $partnerBD->getId() : null,
            'name' => $partnerBD ? $partnerBD->getName() : null,
            'firstName' => $partnerBD ?  $partnerBD->getFirstname() : null,
            'selectedGender' => $partnerBD ? $partnerBD->getGender() : null,
            'nationality' => $partnerBD ? $partnerBD->getNationality(): null,
            'phoneNumber' => $partnerBD ? $partnerBD->getPhoneNumber() : null,
            'job' => $partnerBD ? $partnerBD->getJob() : null,
            'city' => $locationBD ? $locationBD->getCity() : null,
            'country' => $locationBD ? $locationBD->getCountry() : null,
            'zipCode' => $locationBD ? $locationBD->getZipCode() : null,
            'address' => $locationBD ? $locationBD->getAddress() : null,
        ];
        return $this->json($data);
    }



    #[Route('/create/profile', name: 'create_profile_recruiter', methods: ['POST'])]
    public function createProfile(
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {

        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json('Missing required data', 404);
        }

        $partner = new Partner();
        $partner->setName($data['name']);
        $partner->setFirstName($data['firstName']);
        $partner->setGender($data['selectedGender']);
        $partner->setNationality($data['nationality']);
        $partner->setPhoneNumber($data['phoneNumber']);
        $location = new Location();
        $location->setCity($data['city']);
        $location->setCountry($data['country']);
        $location->setZipCode($data['zipCode']);
        $partner->setJob($data['job']);
        $partner->setUser($userActuel);
        $location->setUserId($userActuel);


        $entityManager->persist($partner);
        $entityManager->persist($location);
        $entityManager->flush();

        return $this->json('Created new profile successfully', 200);
    }





    #[Route("/edit/profile", name: "edit_profile_recruiter", methods: ["PUT", "PATCH"])]
    public function edit(EntityManagerInterface $entityManager, Request $request): Response
    {
        $userActuel = $this->getUser();

        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }

        try {
            $partnerEdit = $entityManager->getRepository(Partner::class)->findOneBy(['user' => $userActuel]);
            $locationEdit = $entityManager->getRepository(Location::class)->findOneBy(['user' => $userActuel]);

            if (!$partnerEdit || !$locationEdit) {
                return $this->json('Profile not found', 404);
            }

            $content = json_decode($request->getContent(), true);
            if (!$content) {
                return $this->json('Missing required data', 404);
            }
            $partnerEdit->setName($content['name']);
            $partnerEdit->setFirstname($content['firstName']);
            $partnerEdit->setGender($content['gender']);
            $partnerEdit->setNationality($content['nationality']);
            $partnerEdit->setPhoneNumber($content['phoneNumber']);
            $locationEdit->setCity($content['city']);
            $locationEdit->setCountry($content['country']);
            $locationEdit->setZipCode($content['zipCode']);
            $locationEdit->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json('Changing data successfully', 200);
        } catch (\Exception $e) {
            return $this->json($e);
        }
    }
}
