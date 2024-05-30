<?php

namespace App\Controller\Location;


use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LocationController extends AbstractController
{

    private $locationRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->locationRepository = $entityManager->getRepository(Location::class);
    }


    #[Route("/list-location", name: "all_list_location", methods: ["GET"])]
    public function listOfAll(): Response
    {

        $locationBD = $this->locationRepository->findAll();

        $data = [];

        foreach ($locationBD as $location) {
            $data[] = [
                'id' => $location->getId(),
                'address' => $location->getAddress(),
                'city' =>  $location->getCity(),
                'country' =>  $location->getCountry(),
            ];
        }

        return $this->json($data);
    }
    

    #[Route("/candidate/list-location", name: "list_location", methods: ["GET"])]
    public function index(): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }

        $locationBD = $this->locationRepository->findBy(['user' => $userActuel]);

        $data = [];

        foreach ($locationBD as $location) {
            $data[] = [
                'id' => $location->getId(),
                'address' => $location->getAddress(),
                'city' =>  $location->getCity(),
                'street' =>  $location->getCountry(),
                'country' =>  $location->getStreet(),
                'zip_code' =>  $location->getZipCode(),
            ];
        }

        return $this->json($data);
    }



    #[Route("/candidate/exist-location", name: "exist_location", methods: ["GET"])]
    public function exist(): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }
        $locationBD = $this->locationRepository->findOneBy(['user' => $userActuel]);

        $userExists = $locationBD ? true : null;

        return new JsonResponse(['user' => $userExists]);
    }



    #[Route("/candidate/show-location", name: "show_location", methods: ["GET"])]
    public function locationList(): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }
        $locationEdit = $this->locationRepository->findOneBy(['user' => $userActuel]);

        if (!$locationEdit) {
            return $this->json('No location found for id', 404);
        }

        $data =  [
            'id' => $locationEdit->getId(),
            'address' =>  $locationEdit->getAddress(),
            'city' =>  $locationEdit->getCity(),
            'street' =>  $locationEdit->getCountry(),
            'country' =>  $locationEdit->getStreet(),
            'zip_code' =>  $locationEdit->getZipCode(),
        ];

        return $this->json($data);
    }


    #[Route('/candidate/create-location', name: 'location', methods: ['POST'])]
    public function createJobOffer(EntityManagerInterface $entityManager, Request $request): Response
    {

        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }

        $location = new Location;

        $location->setAddress($request->request->get('address'));
        $location->setCity($request->request->get('city'));
        $location->setStreet($request->request->get('street'));
        $location->setCountry($request->request->get('country'));
        $location->setZipCode($request->request->get('zip_code'));

        $location->setUserId($userActuel);

        $entityManager->persist($location);
        $entityManager->flush();

        return new JsonResponse('Created new location successfully with id '. $location->getId());
    }


    #[Route("/candidate/edit-location", name: "edit_location", methods: ["PUT", "PATCH"])]
    public function edit(EntityManagerInterface $entityManager, Request $request): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }
        $locationEdit = $this->locationRepository->findOneBy(['user' => $userActuel]);

        if (!$locationEdit) {
            return $this->json('No location found for id', 404);
        }

        $content = json_decode($request->getContent());
        $locationEdit->setAddress($content->address);
        $locationEdit->setCity($content->city);
        $locationEdit->setStreet($content->street);
        $locationEdit->setCountry($content->country);
        $locationEdit->setZipCode($content->zip_code);

        $entityManager->flush();

        $data =  [
            'id' => $locationEdit->getId(),
            'address' =>  $locationEdit->getAddress(),
            'city' =>  $locationEdit->getCity(),
            'street' =>  $locationEdit->getCountry(),
            'country' =>  $locationEdit->getStreet(),
            'zip_code' =>  $locationEdit->getZipCode(),
        ];

        return $this->json($data);
    }

}
