<?php

/**
 * FoodMarket - Repository: Product
 * 
 * Репозиторий для работы с товарами.
 * Поддерживает фильтрацию по категории, сортировку по цене/названию, пагинацию.
 */

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public const PRODUCTS_PER_PAGE = 50;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Поиск товаров с фильтрацией, сортировкой и пагинацией
     *
     * @param array $params Параметры: categoryId, sortBy, sortOrder, page
     * @return array ['items' => Product[], 'total' => int, 'pages' => int, 'currentPage' => int]
     */
    public function findFiltered(array $params = []): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');

        // фильтр по категории
        if (!empty($params['categoryId'])) {
            $qb->andWhere('p.category = :categoryId')
               ->setParameter('categoryId', $params['categoryId']);
        }

        // поиск по названию
        if (!empty($params['search'])) {
            $qb->andWhere('LOWER(p.name) LIKE :search')
               ->setParameter('search', '%' . mb_strtolower($params['search']) . '%');
        }

        // фильтр по цене
        if (isset($params['minPrice']) && $params['minPrice'] !== '') {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', (float) $params['minPrice']);
        }
        if (isset($params['maxPrice']) && $params['maxPrice'] !== '') {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', (float) $params['maxPrice']);
        }

        // сортировка (по умолчанию - по названию)
        $sortBy = in_array($params['sortBy'] ?? '', ['price', 'name']) ? $params['sortBy'] : 'name';
        $sortOrder = strtoupper($params['sortOrder'] ?? '') === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy('p.' . $sortBy, $sortOrder);

        // пагинация
        $page = max(1, (int) ($params['page'] ?? 1));
        $qb->setFirstResult(($page - 1) * self::PRODUCTS_PER_PAGE)
           ->setMaxResults(self::PRODUCTS_PER_PAGE);

        $items = $qb->getQuery()->getResult();

        // подсчёт общего количества для пагинации
        $countQb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)');
        if (!empty($params['categoryId'])) {
            $countQb->andWhere('p.category = :categoryId')
                    ->setParameter('categoryId', $params['categoryId']);
        }
        if (!empty($params['search'])) {
            $countQb->andWhere('LOWER(p.name) LIKE :search')
                    ->setParameter('search', '%' . mb_strtolower($params['search']) . '%');
        }
        if (isset($params['minPrice']) && $params['minPrice'] !== '') {
            $countQb->andWhere('p.price >= :minPrice')
                    ->setParameter('minPrice', (float) $params['minPrice']);
        }
        if (isset($params['maxPrice']) && $params['maxPrice'] !== '') {
            $countQb->andWhere('p.price <= :maxPrice')
                    ->setParameter('maxPrice', (float) $params['maxPrice']);
        }
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'items' => $items,
            'total' => $total,
            'pages' => (int) ceil($total / self::PRODUCTS_PER_PAGE),
            'currentPage' => $page,
            'perPage' => self::PRODUCTS_PER_PAGE,
        ];
    }
}
