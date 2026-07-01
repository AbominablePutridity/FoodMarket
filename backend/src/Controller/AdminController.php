<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Repository\CategoryRepository;
use App\Repository\ProductImageRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

class AdminController extends AbstractController
{
    private string $uploadDir;

    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private ProductImageRepository $productImageRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
        $this->uploadDir = __DIR__ . '/../../public/uploads/products';
    }

    private function getUploadDir(): string
    {
        $dir = $this->uploadDir;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    public function createProduct(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['price']) || empty($data['deliveryDate']) || empty($data['categoryId'])) {
            return $this->json(['error' => 'Все поля (name, price, deliveryDate, categoryId) обязательны'], 400);
        }

        $category = $this->categoryRepository->find($data['categoryId']);
        if (!$category) {
            return $this->json(['error' => 'Категория не найдена'], 400);
        }

        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice((string) $data['price']);
        $product->setDeliveryDate(new \DateTime($data['deliveryDate']));
        $product->setCategory($category);

        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Товар успешно создан',
            'product' => json_decode($this->serializer->serialize($product, 'json', [
                AbstractNormalizer::GROUPS => ['product:read'],
            ])),
        ], 201);
    }

    public function updateProduct(int $id, Request $request): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Некорректный JSON'], 400);
        }

        try {
            if (isset($data['name']) && $data['name'] !== '') {
                $product->setName($data['name']);
            }
            if (isset($data['price']) && $data['price'] !== '' && $data['price'] !== null) {
                $product->setPrice((string) $data['price']);
            }
            if (!empty($data['deliveryDate'])) {
                $product->setDeliveryDate(new \DateTime($data['deliveryDate']));
            }
            if (!empty($data['categoryId'])) {
                $category = $this->categoryRepository->find((int) $data['categoryId']);
                if ($category) {
                    $product->setCategory($category);
                }
            }

            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Ошибка при обновлении: ' . $e->getMessage()], 500);
        }

        return $this->json([
            'message' => 'Товар обновлён',
            'product' => json_decode($this->serializer->serialize($product, 'json', [
                AbstractNormalizer::GROUPS => ['product:read'],
            ])),
        ]);
    }

    public function deleteProduct(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], 404);
        }

        // удаляем файлы изображений с диска
        foreach ($product->getImages() as $image) {
            $filePath = $this->getUploadDir() . '/' . $image->getFilename();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return $this->json(['message' => 'Товар удалён']);
    }

    public function setDiscount(int $id, Request $request): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $discountPercent = (int) ($data['discountPercent'] ?? 0);

        if ($discountPercent < 0 || $discountPercent > 100) {
            return $this->json(['error' => 'Скидка должна быть от 0 до 100 процентов'], 400);
        }

        $product->setDiscountPercent($discountPercent);
        $this->entityManager->flush();

        $discountMessage = $discountPercent > 0
            ? "Скидка {$discountPercent}% назначена на товар"
            : 'Скидка убрана';

        return $this->json([
            'message' => $discountMessage,
            'product' => json_decode($this->serializer->serialize($product, 'json', [
                AbstractNormalizer::GROUPS => ['product:read'],
            ])),
        ]);
    }

    public function uploadImages(int $id, Request $request): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], 404);
        }

        $files = $request->files->get('images');
        if (!$files) {
            return $this->json(['error' => 'Файлы не загружены. Используйте поле "images" с типом multipart/form-data'], 400);
        }

        if (!is_array($files)) {
            $files = [$files];
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        $uploaded = [];
        $errors = [];

        foreach ($files as $file) {
            if (!$file->isValid()) {
                $errors[] = 'Ошибка загрузки файла';
                continue;
            }

            if ($file->getSize() > $maxFileSize) {
                $errors[] = "Файл слишком большой (макс. 5MB): {$file->getClientOriginalName()}";
                continue;
            }

            $clientMimeType = $file->getClientMimeType();
            if (!in_array($clientMimeType, $allowedMimeTypes)) {
                $errors[] = "Недопустимый тип файла: {$file->getClientOriginalName()}";
                continue;
            }

            $extension = $file->getClientOriginalExtension();
            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $extension = 'jpg';
            }
            $filename = uniqid('img_', true) . '.' . $extension;
            $originalName = $file->getClientOriginalName();

            try {
                $size = $file->getSize();
                $file->move($this->getUploadDir(), $filename);
            } catch (\Exception $e) {
                $errors[] = "Ошибка при загрузке {$originalName}: " . $e->getMessage();
                continue;
            }

            $isPrimary = $product->getImages()->isEmpty();

            $image = new ProductImage();
            $image->setProduct($product);
            $image->setFilename($filename);
            $image->setOriginalName($originalName);
            $image->setMimeType($clientMimeType);
            $image->setSize($size);

            $product->addImage($image);
            $this->entityManager->persist($image);
            $uploaded[] = $image;
        }

        if (!empty($uploaded)) {
            $this->entityManager->flush();
        }

        return $this->json([
            'message' => count($uploaded) > 0 ? 'Изображения загружены' : 'Изображения не загружены',
            'uploaded' => count($uploaded),
            'errors' => $errors,
            'product' => json_decode($this->serializer->serialize($product, 'json', [
                AbstractNormalizer::GROUPS => ['product:read'],
            ])),
        ]);
    }

    public function deleteImage(int $id, int $imageId): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], 404);
        }

        $image = $this->productImageRepository->find($imageId);
        if (!$image || $image->getProduct()->getId() !== $product->getId()) {
            return $this->json(['error' => 'Изображение не найдено'], 404);
        }

        $filePath = $this->getUploadDir() . '/' . $image->getFilename();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $wasPrimary = $image->isPrimary();
        $this->entityManager->remove($image);
        $this->entityManager->flush();

        if ($wasPrimary && !$product->getImages()->isEmpty()) {
            $product->getImages()->first()->setIsPrimary(true);
            $this->entityManager->flush();
        }

        return $this->json([
            'message' => 'Изображение удалено',
            'product' => json_decode($this->serializer->serialize($product, 'json', [
                AbstractNormalizer::GROUPS => ['product:read'],
            ])),
        ]);
    }

    public function setPrimaryImage(int $id, int $imageId): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], 404);
        }

        $image = $this->productImageRepository->find($imageId);
        if (!$image || $image->getProduct()->getId() !== $product->getId()) {
            return $this->json(['error' => 'Изображение не найдено'], 404);
        }

        foreach ($product->getImages() as $img) {
            $img->setIsPrimary(false);
        }
        $image->setIsPrimary(true);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Основное изображение изменено',
            'product' => json_decode($this->serializer->serialize($product, 'json', [
                AbstractNormalizer::GROUPS => ['product:read'],
            ])),
        ]);
    }
}
