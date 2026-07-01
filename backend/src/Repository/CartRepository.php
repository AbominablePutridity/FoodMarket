<?php

/**
 * FoodMarket - Repository: Cart
 * 
 * Репозиторий для работы с корзинами.
 * Позволяет найти активную (неоплаченную) корзину пользователя.
 */

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    /**
     * Находит активную (неоплаченную) корзину пользователя
     * Если корзины нет - возвращает null
     */
    public function findActiveCart(User $user): ?Cart
    {
        return $this->findOneBy([
            'user' => $user,
            'status' => Cart::STATUS_PENDING,
        ]);
    }

    /**
     * Находит или создаёт активную корзину для пользователя
     */
    public function findOrCreateActiveCart(User $user): Cart
    {
        $cart = $this->findActiveCart($user);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->getEntityManager()->persist($cart);
            $this->getEntityManager()->flush();
        }
        return $cart;
    }
}
