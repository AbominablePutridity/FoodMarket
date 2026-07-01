<?php

/**
 * FoodMarket - Service: ReceiptService
 * 
 * Сервис для генерации электронных чеков.
 * Создаёт текстовый документ с таблицей товаров из оплаченной корзины.
 */

namespace App\Service;

use App\Entity\Cart;

class ReceiptService
{
    /**
     * Генерирует чек в виде форматированного текста
     *
     * @param Cart $cart Оплаченная корзина
     * @return string Содержимое чека
     */
    public function generateReceipt(Cart $cart): string
    {
        if ($cart->getStatus() !== Cart::STATUS_PAID) {
            throw new \RuntimeException('Чек можно получить только для оплаченной корзины');
        }

        $user = $cart->getUser();

        // шапка чека
        $receipt = [];
        $receipt[] = str_repeat('=', 80);
        $receipt[] = '                     ЧЕК ОБ ОПЛАТЕ - FoodMarket';
        $receipt[] = str_repeat('=', 80);
        $receipt[] = '';
        $receipt[] = sprintf('Номер заказа:   #%d', $cart->getId());
        $receipt[] = sprintf('Покупатель:     %s', $user->getEmail());
        $receipt[] = sprintf('Дата оплаты:    %s', $cart->getPaidAt()->format('d.m.Y H:i:s'));
        $receipt[] = '';
        $receipt[] = str_repeat('-', 80);
        $receipt[] = sprintf('%-5s | %-40s | %-10s | %-8s | %-10s', '№', 'Наименование', 'Цена', 'Кол-во', 'Сумма');
        $receipt[] = str_repeat('-', 80);

        // товары
        $index = 1;
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $price = (float) $item->getPriceAtTime();
            $quantity = $item->getQuantity();
            $subtotal = $item->getSubtotal();

            $name = mb_substr($product->getName(), 0, 38);
            $receipt[] = sprintf(
                '%-5d | %-40s | %-10.2f | %-8d | %-10.2f',
                $index,
                $name,
                $price,
                $quantity,
                $subtotal
            );
            $index++;
        }

        $receipt[] = str_repeat('-', 80);
        $receipt[] = sprintf('%-68s %-10.2f', 'ИТОГО К ОПЛАТЕ:', (float) $cart->getTotal());
        $receipt[] = str_repeat('-', 80);
        $receipt[] = '';
        $receipt[] = '  Спасибо за покупку! Приходите ещё :)';
        $receipt[] = '';
        $receipt[] = str_repeat('=', 80);

        return implode("\n", $receipt);
    }
}
