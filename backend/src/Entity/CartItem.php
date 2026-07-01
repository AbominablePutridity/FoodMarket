<?php

/**
 * FoodMarket - Entity: CartItem
 * 
 * Позиция в корзине - связывает корзину и товар.
 * Хранит количество товаров и цену на момент добавления.
 */

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Positive(message: 'Количество должно быть положительным')]
    private int $quantity = 1;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $priceAtTime = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cart $cart = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPriceAtTime(): ?string
    {
        return $this->priceAtTime;
    }

    public function setPriceAtTime(string $priceAtTime): self
    {
        $this->priceAtTime = $priceAtTime;
        return $this;
    }

    /**
     * Возвращает стоимость позиции (количество * цена со скидкой)
     */
    public function getSubtotal(): float
    {
        return $this->quantity * (float) $this->priceAtTime;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }
}
