<?php

/**
 * FoodMarket - Controller: CartController
 * 
 * Контроллер для управления корзиной.
 * Доступен только аутентифицированным пользователям (ROLE_USER).
 * Позволяет: просматривать корзину, добавлять/удалять товары,
 * оплачивать корзину и получать электронный чек.
 */

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;
use App\Service\ReceiptService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private CartRepository $cartRepository,
        private ProductRepository $productRepository,
        private CartItemRepository $cartItemRepository,
        private ReceiptService $receiptService,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * Просмотр активной корзины текущего пользователя
     */
    #[OA\Get(
        path: '/api/cart',
        summary: 'Просмотр корзины',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Данные корзины'),
        ]
    )]
    public function show(): JsonResponse
    {
        $user = $this->getUser();
        $cart = $this->cartService->getActiveCart($user);

        $json = $this->serializer->serialize($cart, 'json', [
            AbstractNormalizer::GROUPS => ['cart:read'],
        ]);

        return $this->json(json_decode($json));
    }

    /**
     * Добавить товар в корзину
     */
    #[OA\Post(
        path: '/api/cart/add',
        summary: 'Добавить товар в корзину',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'productId', type: 'integer', example: 1),
                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Товар добавлен'),
            new OA\Response(response: 400, description: 'Ошибка'),
            new OA\Response(response: 404, description: 'Товар не найден'),
        ]
    )]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['productId'])) {
            return $this->json(['error' => 'productId обязателен'], 400);
        }

        $product = $this->productRepository->find($data['productId']);
        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], 404);
        }

        $quantity = max(1, (int) ($data['quantity'] ?? 1));

        $this->cartService->addProduct($this->getUser(), $product, $quantity);

        return $this->json(['message' => 'Товар добавлен в корзину']);
    }

    /**
     * Удалить позицию из корзины
     */
    #[OA\Delete(
        path: '/api/cart/remove/{id}',
        summary: 'Удалить позицию из корзины',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Позиция удалена'),
            new OA\Response(response: 404, description: 'Позиция не найдена'),
        ]
    )]
    public function remove(int $id): JsonResponse
    {
        $item = $this->cartItemRepository->find($id);
        if (!$item) {
            return $this->json(['error' => 'Позиция не найдена'], 404);
        }

        $cart = $item->getCart();
        if ($cart->getUser()->getId() !== $this->getUser()->getId()) {
            return $this->json(['error' => 'Доступ запрещён'], 403);
        }

        $this->cartService->removeItem($cart, $item);

        return $this->json(['message' => 'Позиция удалена из корзины']);
    }

    /**
     * Оплатить корзину
     */
    #[OA\Post(
        path: '/api/cart/pay',
        summary: 'Оплатить корзину',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Корзина оплачена'),
            new OA\Response(response: 400, description: 'Ошибка оплаты'),
        ]
    )]
    public function pay(): JsonResponse
    {
        $user = $this->getUser();
        $cart = $this->cartService->getActiveCart($user);

        try {
            $this->cartService->pay($cart);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json([
            'message' => 'Корзина успешно оплачена',
            'cartId' => $cart->getId(),
        ]);
    }

    /**
     * Получить электронный чек
     * Чек возвращается в виде текстового документа
     */
    #[OA\Get(
        path: '/api/cart/receipt/{id}',
        summary: 'Получить электронный чек',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Текстовый чек'),
            new OA\Response(response: 404, description: 'Корзина не найдена'),
        ]
    )]
    public function receipt(int $id): BinaryFileResponse
    {
        $cart = $this->cartRepository->find($id);

        if (!$cart || $cart->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createNotFoundException('Корзина не найдена');
        }

        try {
            $filePath = $this->receiptService->generateReceipt($cart);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'receipt_' . $cart->getId() . '.docx'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
