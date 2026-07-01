<?php

/**
 * FoodMarket - Controller: ProductController
 * 
 * Публичный контроллер для просмотра товаров.
 * Доступен без аутентификации.
 * Поддерживает фильтрацию по категории, сортировку и пагинацию.
 */

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * Список товаров с фильтрацией, сортировкой и пагинацией
     */
    #[OA\Get(
        path: '/api/products',
        summary: 'Список товаров',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Номер страницы', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'sortBy', in: 'query', description: 'Поле сортировки (name, price)', schema: new OA\Schema(type: 'string', default: 'name')),
            new OA\Parameter(name: 'sortOrder', in: 'query', description: 'Направление (asc, desc)', schema: new OA\Schema(type: 'string', default: 'asc')),
            new OA\Parameter(name: 'categoryId', in: 'query', description: 'ID категории', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Список товаров'),
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $params = [
            'page' => $request->query->get('page', 1),
            'sortBy' => $request->query->get('sortBy', 'name'),
            'sortOrder' => $request->query->get('sortOrder', 'asc'),
            'categoryId' => $request->query->get('categoryId'),
            'search' => $request->query->get('search'),
            'minPrice' => $request->query->get('minPrice'),
            'maxPrice' => $request->query->get('maxPrice'),
        ];

        $result = $this->productRepository->findFiltered($params);

        // сериализуем с нужными группами
        $json = $this->serializer->serialize($result['items'], 'json', [
            AbstractNormalizer::GROUPS => ['product:read'],
        ]);

        return $this->json([
            'products' => json_decode($json),
            'page' => $result['currentPage'],
            'pages' => $result['pages'],
            'total' => $result['total'],
            'perPage' => $result['perPage'],
        ]);
    }

    /**
     * Просмотр одного товара
     */
    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Просмотр товара',
        responses: [
            new OA\Response(response: 200, description: 'Данные товара'),
            new OA\Response(response: 404, description: 'Товар не найден'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], 404);
        }

        $json = $this->serializer->serialize($product, 'json', [
            AbstractNormalizer::GROUPS => ['product:read'],
        ]);

        return $this->json(json_decode($json));
    }
}
