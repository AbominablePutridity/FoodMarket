-- FoodMarket - Seed Data
-- Заполнение базы тестовыми данными
-- Запуск: psql -U foodmarket -d foodmarket -f backend/data/seed.sql
-- Или через консоль: docker exec -i foodmarket-php psql -U foodmarket -d foodmarket < backend/data/seed.sql

-- Отключаем триггеры внешних ключей для очистки
TRUNCATE TABLE product_image RESTART IDENTITY CASCADE;
TRUNCATE TABLE cart_item RESTART IDENTITY CASCADE;
TRUNCATE TABLE cart RESTART IDENTITY CASCADE;
TRUNCATE TABLE product RESTART IDENTITY CASCADE;
TRUNCATE TABLE category RESTART IDENTITY CASCADE;

-- Категории
INSERT INTO category (id, name, slug) VALUES
    (1,  'Молочные продукты',   'molochnye-produkty'),
    (2,  'Мясо и птица',       'myaso-i-ptitsa'),
    (3,  'Овощи',              'ovoshchi'),
    (4,  'Фрукты',             'frukty'),
    (5,  'Хлеб и выпечка',     'khleb-i-vypechka'),
    (6,  'Напитки',            'napitki'),
    (7,  'Бакалея',            'bakaleya'),
    (8,  'Замороженные продукты', 'zamorozhennye-produkty');

-- Сброс sequence для category
ALTER SEQUENCE category_id_seq RESTART WITH 9;

-- Товары
INSERT INTO product (id, category_id, name, price, delivery_date, discount_percent, created_at) VALUES
    -- Молочные продукты (категория 1)
    (1,  1, 'Молоко 3.2% "Домик в деревне" 1л',               89.90,  CURRENT_DATE + 1,  0, NOW()),
    (2,  1, 'Кефир 2.5% "Простоквашино" 1л',                 72.50,  CURRENT_DATE + 1,  5, NOW()),
    (3,  1, 'Сметана 20% "Брест-Литовск" 400г',              119.90, CURRENT_DATE + 2,  0, NOW()),
    (4,  1, 'Творог 5% "Савушкин" 300г',                     89.90,  CURRENT_DATE + 1,  10, NOW()),
    (5,  1, 'Масло сливочное 82.5% "Экомилк" 180г',          159.90, CURRENT_DATE + 3,  0, NOW()),
    (6,  1, 'Сыр "Гауда" 45% 300г',                          299.90, CURRENT_DATE + 4,  15, NOW()),
    (7,  1, 'Йогурт питьевой "Чудо" клубника 270г',           42.50,  CURRENT_DATE + 1,  0, NOW()),
    (8,  1, 'Ряженка 3.2% "Вкуснотеево" 500г',                48.90,  CURRENT_DATE + 2,  0, NOW()),

    -- Мясо и птица (категория 2)
    (9,  2, 'Куриное филе "Петруха" 1кг',                    299.00, CURRENT_DATE + 1,  20, NOW()),
    (10, 2, 'Свинина шейка "Мираторг" 1кг',                  459.00, CURRENT_DATE + 2,  0, NOW()),
    (11, 2, 'Говядина стейк "Prime" 300г',                   699.00, CURRENT_DATE + 3,  0, NOW()),
    (12, 2, 'Фарш говяжий "Натуральный" 500г',              249.00, CURRENT_DATE + 1,  10, NOW()),
    (13, 2, 'Колбаса "Докторская" ГОСТ 500г',               199.00, CURRENT_DATE + 4,  5, NOW()),
    (14, 2, 'Крылья куриные острые 1кг',                     249.00, CURRENT_DATE + 1,  0, NOW()),

    -- Овощи (категория 3)
    (15, 3, 'Картофель мытый 1кг',                           39.90,  CURRENT_DATE + 1,  0, NOW()),
    (16, 3, 'Морковь мытая 1кг',                             29.90,  CURRENT_DATE + 1,  0, NOW()),
    (17, 3, 'Лук репчатый 1кг',                              24.90,  CURRENT_DATE + 2,  0, NOW()),
    (18, 3, 'Помидоры черри 250г',                           89.90,  CURRENT_DATE + 1,  25, NOW()),
    (19, 3, 'Огурцы короткоплодные 1кг',                      59.90,  CURRENT_DATE + 1,  0, NOW()),
    (20, 3, 'Капуста белокочанная 1кг',                       19.90,  CURRENT_DATE + 3,  0, NOW()),

    -- Фрукты (категория 4)
    (21, 4, 'Бананы 1кг',                                    69.90,  CURRENT_DATE + 1,  0, NOW()),
    (22, 4, 'Яблоки "Голден" 1кг',                           79.90,  CURRENT_DATE + 1,  0, NOW()),
    (23, 4, 'Апельсины 1кг',                                 89.90,  CURRENT_DATE + 2,  30, NOW()),
    (24, 4, 'Виноград красный 500г',                          99.90,  CURRENT_DATE + 1,  0, NOW()),
    (25, 4, 'Авокадо Хасс 1шт',                              69.90,  CURRENT_DATE + 3,  0, NOW()),

    -- Хлеб и выпечка (категория 5)
    (26, 5, 'Хлеб "Бородинский" 300г',                       39.90,  CURRENT_DATE + 1,  0, NOW()),
    (27, 5, 'Батон нарезной 400г',                            29.90,  CURRENT_DATE + 1,  0, NOW()),
    (28, 5, 'Круассан с шоколадом 90г',                      49.90,  CURRENT_DATE + 1,  0, NOW()),
    (29, 5, 'Пирожок с картошкой 100г',                      29.90,  CURRENT_DATE + 1,  0, NOW()),

    -- Напитки (категория 6)
    (30, 6, 'Вода минеральная "Боржоми" 0.5л',               59.90,  CURRENT_DATE + 1,  0, NOW()),
    (31, 6, 'Сок яблочный "Добрый" 1л',                      69.90,  CURRENT_DATE + 2,  10, NOW()),
    (32, 6, 'Кока-Кола 0.5л',                                39.90,  CURRENT_DATE + 1,  0, NOW()),
    (33, 6, 'Чай чёрный "Lipton" 25 пак',                    79.90,  CURRENT_DATE + 4,  0, NOW()),

    -- Бакалея (категория 7)
    (34, 7, 'Макароны "Barilla" №5 500г',                    69.90,  CURRENT_DATE + 2,  0, NOW()),
    (35, 7, 'Рис круглозёрный "Националь" 1кг',              59.90,  CURRENT_DATE + 1,  0, NOW()),
    (36, 7, 'Масло подсолнечное "Золотая семечка" 1л',       89.90,  CURRENT_DATE + 3,  15, NOW()),
    (37, 7, 'Соль "Экстра" 1кг',                             14.90,  CURRENT_DATE + 5,  0, NOW()),
    (38, 7, 'Сахар песок 1кг',                               49.90,  CURRENT_DATE + 1,  0, NOW()),

    -- Замороженные продукты (категория 8)
    (39, 8, 'Пельмени "Цезарь" 800г',                        249.00, CURRENT_DATE + 1,  10, NOW()),
    (40, 8, 'Мороженое "Пломбир" ваниль 500г',               119.90, CURRENT_DATE + 2,  0, NOW()),
    (41, 8, 'Зелёный горошек "Bonduelle" 400г',              79.90,  CURRENT_DATE + 1,  0, NOW()),
    (42, 8, 'Блинчики с мясом 450г',                         149.00, CURRENT_DATE + 1,  0, NOW());

-- Сброс sequence для product
ALTER SEQUENCE product_id_seq RESTART WITH 43;

-- Примечание: тестовые изображения загружаются через админ-панель
-- или создаются автоматически командой: php bin/console app:seed
