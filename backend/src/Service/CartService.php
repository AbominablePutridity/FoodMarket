<?php

/**
 * FoodMarket - Service: CartService
 * 
 * Сервис для управления корзиной.
 * Содержит бизнес-логику добавления/удаления товаров,
 * расчёта суммы и оплаты корзины.
 */

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    public function __construct(
        private CartRepository $cartRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Возвращает активную корзину пользователя
     */
    public function getActiveCart(User $user): Cart
    {
        return $this->cartRepository->findOrCreateActiveCart($user);
    }

    /**
     * Добавляет товар в корзину
     *
     * @param User $user Пользователь
     * @param Product $product Товар
     * @param int $quantity Количество
     * @return Cart Корзина с обновлёнными данными
     */
    public function addProduct(User $user, Product $product, int $quantity = 1): Cart
    {
        $cart = $this->getActiveCart($user);

        // создаём позицию корзины
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity($quantity);
        // фиксируем цену со скидкой на момент добавления
        $item->setPriceAtTime((string) $product->getDiscountedPrice());

        $cart->addItem($item);
        $cart->recalculateTotal();

        $this->entityManager->flush();

        return $cart;
    }

    /**
     * Удаляет позицию из корзины
     */
    public function removeItem(Cart $cart, CartItem $item): void
    {
        $cart->removeItem($item);
        $cart->recalculateTotal();

        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }

    /**
     * Оплачивает корзину
     * Меняет статус на "paid" и фиксирует дату оплаты
     *
     * @return Cart Корзина со статусом "paid"
     */
    public function pay(Cart $cart): Cart
    {
        if ($cart->getStatus() === Cart::STATUS_PAID) {
            throw new \RuntimeException('Корзина уже оплачена');
        }

        if ($cart->getItems()->isEmpty()) {
            throw new \RuntimeException('Нельзя оплатить пустую корзину');
        }

        $cart->setStatus(Cart::STATUS_PAID);
        $cart->setPaidAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $cart;
    }
}
