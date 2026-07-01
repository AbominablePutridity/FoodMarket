<?php

namespace App\Service;

use App\Entity\Cart;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class ReceiptService
{
    public function generateReceipt(Cart $cart): string
    {
        if ($cart->getStatus() !== Cart::STATUS_PAID) {
            throw new \RuntimeException('Чек можно получить только для оплаченной корзины');
        }

        $user = $cart->getUser();
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'orientation' => 'portrait',
            'marginLeft'  => Converter::cmToTwip(2),
            'marginRight' => Converter::cmToTwip(2),
            'marginTop'   => Converter::cmToTwip(2),
            'marginBottom'=> Converter::cmToTwip(2),
        ]);

        $section->addText(
            'ЧЕК ОБ ОПЛАТЕ — FoodMarket',
            ['bold' => true, 'size' => 16, 'color' => '2b7a3a'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 240]
        );

        $section->addText(
            sprintf('Заказ #%d', $cart->getId()),
            ['bold' => true, 'size' => 12],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );

        $styleName = 'ReceiptTable';
        $phpWord->addTableStyle($styleName, [
            'borderSize'  => 6,
            'borderColor' => '999999',
            'cellMargin'  => 80,
        ], [
            'header' => ['bold' => true, 'size' => 10, 'bgColor' => 'e8f5e9'],
        ]);

        $section->addText(
            sprintf('Покупатель: %s', $user->getEmail()),
            ['size' => 10],
            ['spaceAfter' => 60]
        );
        $section->addText(
            sprintf('Дата оплаты: %s', $cart->getPaidAt()->format('d.m.Y H:i:s')),
            ['size' => 10],
            ['spaceAfter' => 200]
        );

        $table = $section->addTable($styleName);
        $table->addRow();
        $table->addCell(600)->addText('№', ['bold' => true], ['alignment' => Jc::CENTER]);
        $table->addCell(5000)->addText('Наименование', ['bold' => true]);
        $table->addCell(1800)->addText('Цена, ₽', ['bold' => true], ['alignment' => Jc::RIGHT]);
        $table->addCell(1000)->addText('Кол-во', ['bold' => true], ['alignment' => Jc::CENTER]);
        $table->addCell(1800)->addText('Сумма, ₽', ['bold' => true], ['alignment' => Jc::RIGHT]);

        $index = 1;
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $price = (float) $item->getPriceAtTime();
            $quantity = $item->getQuantity();
            $subtotal = $item->getSubtotal();

            $table->addRow();
            $table->addCell(600)->addText((string) $index, null, ['alignment' => Jc::CENTER]);
            $table->addCell(5000)->addText($product->getName());
            $table->addCell(1800)->addText(number_format($price, 2, ',', ' '), null, ['alignment' => Jc::RIGHT]);
            $table->addCell(1000)->addText((string) $quantity, null, ['alignment' => Jc::CENTER]);
            $table->addCell(1800)->addText(number_format($subtotal, 2, ',', ' '), null, ['alignment' => Jc::RIGHT]);
            $index++;
        }

        $section->addText('', null, ['spaceBefore' => 120]);

        $section->addText(
            sprintf('ИТОГО к оплате: %s ₽', number_format((float) $cart->getTotal(), 2, ',', ' ')),
            ['bold' => true, 'size' => 12, 'color' => '2b7a3a'],
            ['alignment' => Jc::RIGHT, 'spaceAfter' => 200]
        );

        $section->addText(
            'Спасибо за покупку! Приходите ещё :)',
            ['italic' => true, 'size' => 10, 'color' => '666666'],
            ['alignment' => Jc::CENTER]
        );

        $tempFile = tempnam(sys_get_temp_dir(), 'receipt_') . '.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return $tempFile;
    }
}
