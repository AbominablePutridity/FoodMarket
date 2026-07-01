<?php

/**
 * FoodMarket - Entity: Category
 * 
 * Категория товаров (Молочка, Мясо, Овощи, Фрукты и т.д.).
 * Каждая категория имеет уникальное название и slug для URL.
 */

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Название категории не может быть пустым')]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class)]
    private Collection $products;

    /**
     * Конструктор - инициализируем коллекцию товаров
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        // автоматически генерируем slug из названия
        $this->slug = mb_strtolower(trim(preg_replace('/[^a-zA-Zа-яА-Я0-9]+/u', '-', $name)), 'UTF-8');
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }
}
