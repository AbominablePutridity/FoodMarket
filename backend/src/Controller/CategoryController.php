<?php

/**
 * FoodMarket - Controller: CategoryController
 * 
 * Публичный контроллер для просмотра категорий товаров.
 * Доступен без аутентификации.
 */

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * Список всех категорий
     */
    #[OA\Get(
        path: '/api/categories',
        summary: 'Список категорий товаров',
        responses: [
            new OA\Response(response: 200, description: 'Список категорий'),
        ]
    )]
    public function list(): JsonResponse
    {
        $categories = $this->categoryRepository->findAllSorted();

        $json = $this->serializer->serialize($categories, 'json', [
            AbstractNormalizer::GROUPS => ['category:read'],
        ]);

        return $this->json(json_decode($json));
    }
}
