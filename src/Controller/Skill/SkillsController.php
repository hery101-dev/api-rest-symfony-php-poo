<?php

namespace App\Controller\Skill;

use App\Entity\Profile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


#[Route('/page-skill', name: 'skills_')]
class SkillsController extends AbstractController
{

    #[Route("/skill", name: "list", methods: ["GET"])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }

        $skillsBD = $entityManager->getRepository(Profile::class)->findBy(['user' => $userActuel]);

        $data = [];

        foreach ($skillsBD as $product) {
            $data[] = [
                'id' => $product->getId(),
                'speciality' => $product->getSpeciality(),
                'experience' => $product->getExperience(),
                'formation' => $product->getFormation(),
                'skills' => $product->getSkills(),
                'spare_time' => $product->getSpareTime(),
                'biography' => $product->getBiography(),
            ];
        }

        return $this->json($data);
    }

    #[Route("/get", name: "show_skills", methods: ["GET"])]
    public function show(EntityManagerInterface $entityManager): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }

        $show_skills = $entityManager->getRepository(Profile::class)->findOneBy(['user' => $userActuel]);

        if (!$show_skills) {
            return $this->json('No skills found for id', 404);
        }

        $data =  [
            'id' => $show_skills->getId(),
            'speciality' => $show_skills->getSpeciality(),
            'experience' => $show_skills->getExperience(),
            'formation' => $show_skills->getFormation(),
            'skills' => $show_skills->getSkills(),
            'spare_time' => $show_skills->getSpareTime(),
            'biography' => $show_skills->getBiography(),

        ];

        return $this->json($data);
    }


    #[Route("/exist", name: "exist", methods: ["GET"])]
    public function exist(EntityManagerInterface $entityManager): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }

        $skillsBD = $entityManager->getRepository(Profile::class)->findOneBy(['user' => $userActuel]);

        $userExists = $skillsBD ? true : null;

        return new JsonResponse(['user' => $userExists]);
    }



    #[Route('/create-skill', name: 'create', methods: ['POST'])]
    public function createSkills(
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {

        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }

        $profile = new Profile();
        $profile->setSpeciality($request->request->get('speciality'));
        $profile->setExperience($request->request->get('experience'));
        $profile->setFormation($request->request->get('formation'));
        $profile->setSkills($request->request->get('skills'));
        $profile->setSpareTime($request->request->get('spare_time'));
        $profile->setBiography($request->request->get('biography'));
        $profile->setUser($userActuel);

        $entityManager->persist($profile);
        $entityManager->flush();

        return $this->json('Created new skills candidate successfully with id ' . $profile->getId());
    }




    #[Route("/edit", name: "edit", methods: ["PUT", "PATCH"])]
    public function edit(EntityManagerInterface $entityManager, Request $request): Response
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('No user does not exist', 404);
        }

        $profileEdit = $entityManager->getRepository(Profile::class)->findOneBy(['user' => $userActuel]);

        if (!$profileEdit) {
            return $this->json('No skills found for id', 404);
        }

        $content = json_decode($request->getContent());
        $profileEdit->setSpeciality($content->speciality);
        $profileEdit->setExperience($content->experience);
        $profileEdit->setFormation($content->formation);
        $profileEdit->setSkills($content->skills);
        $profileEdit->setSpareTime($content->spare_time);
        $profileEdit->setBiography($content->biography);

        $entityManager->flush();

        $data =  [
            'id' => $profileEdit->getId(),
            'speciality' => $profileEdit->getSpeciality(),
            'experience' => $profileEdit->getExperience(),
            'formation' => $profileEdit->getFormation(),
            'skills' => $profileEdit->getSkills(),
            'spare_time' => $profileEdit->getSpareTime(),
            'biography' => $profileEdit->getBiography(),
        ];

        return $this->json($data);
    }
}
