<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed', description: 'Заполняет БД тестовыми данными')]
class SeedDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->entityManager;

        $io->title('FoodMarket: заполнение тестовыми данными');

        if ($io->confirm('Будет удалены все существующие данные. Продолжить?', false)) {
            $io->warning('Очистка базы данных...');
            $em->getConnection()->executeStatement('TRUNCATE TABLE product_image RESTART IDENTITY CASCADE');
            $em->getConnection()->executeStatement('TRUNCATE TABLE cart_item RESTART IDENTITY CASCADE');
            $em->getConnection()->executeStatement('TRUNCATE TABLE cart RESTART IDENTITY CASCADE');
            $em->getConnection()->executeStatement('TRUNCATE TABLE product RESTART IDENTITY CASCADE');
            $em->getConnection()->executeStatement('TRUNCATE TABLE category RESTART IDENTITY CASCADE');
        }

        $categories = [];
        $catData = [
            ['Молочные продукты'],
            ['Мясо и птица'],
            ['Овощи'],
            ['Фрукты'],
            ['Хлеб и выпечка'],
            ['Напитки'],
            ['Бакалея'],
            ['Замороженные продукты'],
        ];

        $io->section('Создание категорий...');
        $io->progressStart(count($catData));
        foreach ($catData as [$name]) {
            $cat = new Category();
            $cat->setName($name);
            $em->persist($cat);
            $categories[] = $cat;
            $io->progressAdvance();
        }
        $em->flush();
        $io->progressFinish();
        $io->info(sprintf('Создано %d категорий', count($categories)));

        $productsData = [
            [0, 'Молоко 3.2% "Домик в деревне" 1л',             89.90,  1, 0],
            [0, 'Кефир 2.5% "Простоквашино" 1л',               72.50,  1, 5],
            [0, 'Сметана 20% "Брест-Литовск" 400г',            119.90, 2, 0],
            [0, 'Творог 5% "Савушкин" 300г',                   89.90,  1, 10],
            [0, 'Масло сливочное 82.5% "Экомилк" 180г',         159.90, 3, 0],
            [0, 'Сыр "Гауда" 45% 300г',                        299.90, 4, 15],
            [0, 'Йогурт питьевой "Чудо" клубника 270г',         42.50,  1, 0],
            [0, 'Ряженка 3.2% "Вкуснотеево" 500г',              48.90,  2, 0],
            [1, 'Куриное филе "Петруха" 1кг',                  299.00,  1, 20],
            [1, 'Свинина шейка "Мираторг" 1кг',                 459.00,  2, 0],
            [1, 'Говядина стейк "Prime" 300г',                 699.00,  3, 0],
            [1, 'Фарш говяжий "Натуральный" 500г',             249.00,  1, 10],
            [1, 'Колбаса "Докторская" ГОСТ 500г',              199.00,  4, 5],
            [1, 'Крылья куриные острые 1кг',                   249.00,  1, 0],
            [2, 'Картофель мытый 1кг',                         39.90,   1, 0],
            [2, 'Морковь мытая 1кг',                           29.90,   1, 0],
            [2, 'Лук репчатый 1кг',                            24.90,   2, 0],
            [2, 'Помидоры черри 250г',                         89.90,   1, 25],
            [2, 'Огурцы короткоплодные 1кг',                   59.90,   1, 0],
            [2, 'Капуста белокочанная 1кг',                    19.90,   3, 0],
            [3, 'Бананы 1кг',                                  69.90,   1, 0],
            [3, 'Яблоки "Голден" 1кг',                         79.90,   1, 0],
            [3, 'Апельсины 1кг',                               89.90,   2, 30],
            [3, 'Виноград красный 500г',                       99.90,   1, 0],
            [3, 'Авокадо Хасс 1шт',                            69.90,   3, 0],
            [4, 'Хлеб "Бородинский" 300г',                     39.90,   1, 0],
            [4, 'Батон нарезной 400г',                         29.90,   1, 0],
            [4, 'Круассан с шоколадом 90г',                    49.90,   1, 0],
            [4, 'Пирожок с картошкой 100г',                    29.90,   1, 0],
            [5, 'Вода минеральная "Боржоми" 0.5л',             59.90,   1, 0],
            [5, 'Сок яблочный "Добрый" 1л',                    69.90,   2, 10],
            [5, 'Кока-Кола 0.5л',                              39.90,   1, 0],
            [5, 'Чай чёрный "Lipton" 25 пак',                  79.90,   4, 0],
            [6, 'Макароны "Barilla" №5 500г',                  69.90,   2, 0],
            [6, 'Рис круглозёрный "Националь" 1кг',            59.90,   1, 0],
            [6, 'Масло подсолнечное "Золотая семечка" 1л',     89.90,   3, 15],
            [6, 'Соль "Экстра" 1кг',                           14.90,   5, 0],
            [6, 'Сахар песок 1кг',                             49.90,   1, 0],
            [7, 'Пельмени "Цезарь" 800г',                      249.00,  1, 10],
            [7, 'Мороженое "Пломбир" ваниль 500г',             119.90,  2, 0],
            [7, 'Зелёный горошек "Bonduelle" 400г',            79.90,   1, 0],
            [7, 'Блинчики с мясом 450г',                       149.00,  1, 0],
        ];

        $io->section('Создание товаров...');
        $io->progressStart(count($productsData));
        foreach ($productsData as [$catIdx, $name, $price, $deliveryOffset, $discount]) {
            $product = new Product();
            $product->setName($name);
            $product->setPrice((string) $price);
            $product->setDeliveryDate(new \DateTime(sprintf('+%d days', $deliveryOffset)));
            $product->setCategory($categories[$catIdx]);
            if ($discount > 0) {
                $product->setDiscountPercent($discount);
            }
            $em->persist($product);
            $io->progressAdvance();
        }
        $em->flush();
        $io->progressFinish();
        $io->info(sprintf('Создано %d товаров', count($productsData)));

        $io->success('Готово! Данные загружены.');
        $io->note('Изображения не созданы. Для загрузки фото используйте админ-панель.');
        return Command::SUCCESS;
    }
}
