<?php

namespace App\Controller\Category;

use App\Entity\Categories;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class CategoryController extends AbstractController
{

    private $categoriesRepository;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->categoriesRepository = $this->entityManager->getRepository(Categories::class);
    }

    #[Route('/category-list', name: 'category_list', methods: ['GET'])]
    public function categories(): JsonResponse
    {
        $categories = $this->categoriesRepository->findAll();

        $categoryData = [];

        foreach ($categories as $category) {
            $categoryData[] = [
                'id' => $category->getId(),
                'category_name' => $category->getCategoryName()
            ];
        }
        return $this->json($categoryData);
    }
}
