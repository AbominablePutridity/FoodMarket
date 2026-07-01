-- =============================================================
-- FoodMarket - Тестовые данные
-- =============================================================
-- Заполняет базу категориями и товарами для демонстрации.
-- Запускать после миграций:
--   docker exec foodmarket-postgres psql -U foodmarket -d foodmarket -f /seed.sql
-- =============================================================

-- Категории
INSERT INTO category (id, name, slug) VALUES
    (nextval('category_id_seq'), 'Молочная продукция', 'molochnaya-produktsiya'),
    (nextval('category_id_seq'), 'Мясо и птица', 'myaso-i-ptitsa'),
    (nextval('category_id_seq'), 'Овощи и фрукты', 'ovoshchi-i-frukty'),
    (nextval('category_id_seq'), 'Хлеб и выпечка', 'khleb-i-vypechka'),
    (nextval('category_id_seq'), 'Напитки', 'napitki'),
    (nextval('category_id_seq'), 'Бакалея', 'bakaleya');

-- Молочная продукция (category_id = 1)
INSERT INTO product (id, name, price, delivery_date, discount_percent, category_id, created_at) VALUES
    (nextval('product_id_seq'), 'Молоко 3.2% "Домик в деревне"', 89.90, '2025-07-15', 0, 1, NOW()),
    (nextval('product_id_seq'), 'Молоко 2.5% "Простоквашино"', 79.90, '2025-07-14', 5, 1, NOW()),
    (nextval('product_id_seq'), 'Кефир 3.2% 1л', 95.00, '2025-07-15', 0, 1, NOW()),
    (nextval('product_id_seq'), 'Сметана 20% 300г', 120.00, '2025-07-13', 10, 1, NOW()),
    (nextval('product_id_seq'), 'Творог 9% 400г', 150.00, '2025-07-15', 0, 1, NOW()),
    (nextval('product_id_seq'), 'Сыр "Российский" 300г', 250.00, '2025-07-14', 0, 1, NOW()),
    (nextval('product_id_seq'), 'Масло сливочное 82.5% 180г', 180.00, '2025-07-15', 15, 1, NOW()),
    (nextval('product_id_seq'), 'Йогурт питьевой "Чудо" 0.5л', 65.00, '2025-07-16', 0, 1, NOW()),
    (nextval('product_id_seq'), 'Ряженка 3.5% 0.5л', 55.00, '2025-07-14', 0, 1, NOW()),
    (nextval('product_id_seq'), 'Сгущённое молоко 380г', 110.00, '2025-07-20', 0, 1, NOW());

-- Мясо и птица (category_id = 2)
INSERT INTO product (id, name, price, delivery_date, discount_percent, category_id, created_at) VALUES
    (nextval('product_id_seq'), 'Куриная грудка 1кг', 350.00, '2025-07-15', 0, 2, NOW()),
    (nextval('product_id_seq'), 'Фарш говяжий 500г', 280.00, '2025-07-14', 10, 2, NOW()),
    (nextval('product_id_seq'), 'Свинина шейка 1кг', 420.00, '2025-07-15', 0, 2, NOW()),
    (nextval('product_id_seq'), 'Крылья куриные 1кг', 220.00, '2025-07-13', 20, 2, NOW()),
    (nextval('product_id_seq'), 'Говядина тушёная 500г', 380.00, '2025-07-16', 0, 2, NOW()),
    (nextval('product_id_seq'), 'Колбаса "Докторская" 400г', 290.00, '2025-07-15', 0, 2, NOW()),
    (nextval('product_id_seq'), 'Сосиски молочные 400г', 210.00, '2025-07-14', 5, 2, NOW()),
    (nextval('product_id_seq'), 'Печень куриная 500г', 140.00, '2025-07-15', 0, 2, NOW()),
    (nextval('product_id_seq'), 'Бедро куриное 1кг', 260.00, '2025-07-14', 0, 2, NOW()),
    (nextval('product_id_seq'), 'Котлеты домашние 500г', 250.00, '2025-07-15', 0, 2, NOW());

-- Овощи и фрукты (category_id = 3)
INSERT INTO product (id, name, price, delivery_date, discount_percent, category_id, created_at) VALUES
    (nextval('product_id_seq'), 'Картофель мытый 1кг', 45.00, '2025-07-15', 0, 3, NOW()),
    (nextval('product_id_seq'), 'Помидоры красные 1кг', 120.00, '2025-07-14', 10, 3, NOW()),
    (nextval('product_id_seq'), 'Огурцы свежие 1кг', 90.00, '2025-07-15', 0, 3, NOW()),
    (nextval('product_id_seq'), 'Яблоки "Голден" 1кг', 110.00, '2025-07-13', 0, 3, NOW()),
    (nextval('product_id_seq'), 'Бананы 1кг', 95.00, '2025-07-15', 15, 3, NOW()),
    (nextval('product_id_seq'), 'Морковь 1кг', 35.00, '2025-07-14', 0, 3, NOW()),
    (nextval('product_id_seq'), 'Лук репчатый 1кг', 40.00, '2025-07-16', 0, 3, NOW()),
    (nextval('product_id_seq'), 'Капуста белокочанная 1кг', 50.00, '2025-07-15', 0, 3, NOW()),
    (nextval('product_id_seq'), 'Апельсины 1кг', 130.00, '2025-07-14', 0, 3, NOW()),
    (nextval('product_id_seq'), 'Виноград "Кишмиш" 500г', 170.00, '2025-07-15', 0, 3, NOW());

-- Хлеб и выпечка (category_id = 4)
INSERT INTO product (id, name, price, delivery_date, discount_percent, category_id, created_at) VALUES
    (nextval('product_id_seq'), 'Хлеб "Бородинский" 400г', 55.00, '2025-07-15', 0, 4, NOW()),
    (nextval('product_id_seq'), 'Батон нарезной 400г', 48.00, '2025-07-14', 0, 4, NOW()),
    (nextval('product_id_seq'), 'Булочка с маком 100г', 35.00, '2025-07-15', 0, 4, NOW()),
    (nextval('product_id_seq'), 'Пирожок с картошкой 150г', 45.00, '2025-07-13', 20, 4, NOW()),
    (nextval('product_id_seq'), 'Лаваш тонкий 200г', 40.00, '2025-07-15', 0, 4, NOW()),
    (nextval('product_id_seq'), 'Печенье "Юбилейное" 300г', 85.00, '2025-07-16', 0, 4, NOW()),
    (nextval('product_id_seq'), 'Сухари ванильные 200г', 50.00, '2025-07-14', 0, 4, NOW()),
    (nextval('product_id_seq'), 'Кекс столичный 300г', 95.00, '2025-07-15', 0, 4, NOW()),
    (nextval('product_id_seq'), 'Корж вафельный 200г', 60.00, '2025-07-14', 0, 4, NOW()),
    (nextval('product_id_seq'), 'Пряники "Тульские" 300г', 75.00, '2025-07-15', 0, 4, NOW());

-- Напитки (category_id = 5)
INSERT INTO product (id, name, price, delivery_date, discount_percent, category_id, created_at) VALUES
    (nextval('product_id_seq'), 'Вода минеральная "Боржоми" 0.5л', 65.00, '2025-07-15', 0, 5, NOW()),
    (nextval('product_id_seq'), 'Сок апельсиновый "Добрый" 1л', 110.00, '2025-07-14', 5, 5, NOW()),
    (nextval('product_id_seq'), 'Кола 0.5л', 55.00, '2025-07-15', 0, 5, NOW()),
    (nextval('product_id_seq'), 'Чай зелёный "Greenfield" 100 пак', 180.00, '2025-07-13', 10, 5, NOW()),
    (nextval('product_id_seq'), 'Кофе растворимый "Nescafe" 100г', 250.00, '2025-07-16', 0, 5, NOW()),
    (nextval('product_id_seq'), 'Лимонад "Дюшес" 1.5л', 70.00, '2025-07-15', 0, 5, NOW()),
    (nextval('product_id_seq'), 'Квас "Очаковский" 1л', 85.00, '2025-07-14', 0, 5, NOW()),
    (nextval('product_id_seq'), 'Морс клюквенный 0.5л', 60.00, '2025-07-15', 0, 5, NOW()),
    (nextval('product_id_seq'), 'Пиво "Жигулёвское" 0.5л', 55.00, '2025-07-14', 0, 5, NOW()),
    (nextval('product_id_seq'), 'Энергетик "Burn" 0.5л', 95.00, '2025-07-15', 0, 5, NOW());

-- Бакалея (category_id = 6)
INSERT INTO product (id, name, price, delivery_date, discount_percent, category_id, created_at) VALUES
    (nextval('product_id_seq'), 'Рис круглозёрный 1кг', 85.00, '2025-07-15', 0, 6, NOW()),
    (nextval('product_id_seq'), 'Гречка 1кг', 70.00, '2025-07-14', 10, 6, NOW()),
    (nextval('product_id_seq'), 'Макароны "Макфа" 400г', 65.00, '2025-07-15', 0, 6, NOW()),
    (nextval('product_id_seq'), 'Сахар 1кг', 55.00, '2025-07-13', 0, 6, NOW()),
    (nextval('product_id_seq'), 'Масло подсолнечное 1л', 120.00, '2025-07-15', 0, 6, NOW()),
    (nextval('product_id_seq'), 'Мука пшеничная 1кг', 45.00, '2025-07-14', 0, 6, NOW()),
    (nextval('product_id_seq'), 'Соль "Экстра" 1кг', 25.00, '2025-07-16', 0, 6, NOW()),
    (nextval('product_id_seq'), 'Овсяные хлопья "Геркулес" 500г', 55.00, '2025-07-15', 0, 6, NOW()),
    (nextval('product_id_seq'), 'Конфеты "Коровка" 250г', 95.00, '2025-07-14', 0, 6, NOW()),
    (nextval('product_id_seq'), 'Шоколад "Алёнка" 100г', 80.00, '2025-07-15', 0, 6, NOW());
