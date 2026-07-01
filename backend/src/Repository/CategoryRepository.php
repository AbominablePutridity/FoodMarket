<?php

/**
 * FoodMarket - Repository: Category
 * 
 * Репозиторий для работы с категориями товаров.
 * Категории сортируются по названию по умолчанию.
 */

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Возвращает все категории, отсортированные по названию
     *
     * @return Category[]
     */
    public function findAllSorted(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
