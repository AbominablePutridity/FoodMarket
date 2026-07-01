# 🛒 FoodMarket — Магазин продуктов

Учебный веб-проект: интернет-магазин продуктов с корзиной, оплатой и администрированием.

## 🚀 Быстрый старт

### 1. Запуск через Docker

```bash
# Клонируем репозиторий
git clone <url>
cd FoodMarket

# Запускаем все сервисы (PostgreSQL, PHP, Nginx)
docker-compose up -d --build
```

### 2. Настройка базы данных

```bash
# Выполняем миграции (создание таблиц)
docker exec foodmarket-php php bin/console doctrine:migrations:migrate --no-interaction

# Заполняем тестовыми данными
docker exec -i foodmarket-postgres psql -U foodmarket -d foodmarket < backend/data/seed.sql
```

### 3. Генерация JWT ключей

```bash
docker exec foodmarket-php php bin/console lexik:jwt:generate-keypair
```

### 4. Открываем в браузере

```
http://localhost
```

Swagger UI (документация API): http://localhost/api/doc

---

## 🏗 Архитектура проекта

```
FoodMarket/
├── backend/                    # PHP/Symfony API
│   ├── config/                 # Конфигурации Symfony
│   │   ├── packages/           # Пакеты (Doctrine, Security, JWT, Swagger)
│   │   ├── serialization/      # Настройки сериализации JSON
│   │   └── routes.yaml         # Маршруты API
│   ├── migrations/             # Миграции БД
│   ├── src/
│   │   ├── Controller/         # Контроллеры (обработка запросов)
│   │   ├── Entity/             # Сущности Doctrine (ORM модели)
│   │   ├── Repository/         # Репозитории (запросы к БД)
│   │   └── Service/            # Сервисы (бизнес-логика)
│   ├── public/index.php        # Входная точка
│   ├── Dockerfile              # Образ PHP
│   └── composer.json           # Зависимости PHP
│
├── frontend/                   # Клиентская часть (HTML/JS/CSS)
│   ├── index.html              # Главная страница
│   ├── css/style.css           # Стили
│   └── js/
│       ├── api.js              # HTTP-клиент (работа с API)
│       ├── auth.js             # Авторизация/регистрация
│       ├── cart.js             # Корзина
│       ├── admin.js            # Администрирование
│       └── app.js              # Главный модуль
│
├── docker/
│   ├── nginx/default.conf      # Конфигурация Nginx
│   └── php.ini                 # Настройки PHP
│
├── docker-compose.yml          # Оркестрация контейнеров
└── .github/workflows/ci.yml   # CI/CD Pipeline
```

## 🔧 Технологии

| Компонент | Технология | Назначение |
|-----------|-----------|------------|
| **Backend** | PHP 8.1 + Symfony 6.3 | API сервер |
| **ORM** | Doctrine ORM | Работа с БД |
| **База** | PostgreSQL 15 | Хранение данных |
| **Auth** | JWT (LexikJWTAuthentication) | Аутентификация |
| **API docs** | Swagger UI (NelmioApiDoc) | Документация API |
| **Frontend** | HTML5 + CSS3 + Vanilla JS | Интерфейс |
| **Web server** | Nginx | Прокси и статика |
| **Контейнеры** | Docker + Docker Compose | Развёртывание |

## 📋 API Endpoints

### Публичные (без токена)
| Метод | Путь | Описание |
|-------|------|----------|
| POST | `/api/auth/register` | Регистрация |
| POST | `/api/auth/login` | Вход (JWT) |
| GET | `/api/products` | Список товаров (с пагинацией) |
| GET | `/api/products/{id}` | Просмотр товара |
| GET | `/api/categories` | Список категорий |

### Для авторизованных (нужен JWT)
| Метод | Путь | Описание |
|-------|------|----------|
| GET | `/api/cart` | Просмотр корзины |
| POST | `/api/cart/add` | Добавить в корзину |
| DELETE | `/api/cart/remove/{id}` | Удалить из корзины |
| POST | `/api/cart/pay` | Оплатить корзину |
| GET | `/api/cart/receipt/{id}` | Скачать чек |

### Для администратора (ROLE_ADMIN)
| Метод | Путь | Описание |
|-------|------|----------|
| POST | `/api/admin/products` | Создать товар |
| PUT | `/api/admin/products/{id}` | Обновить товар |
| POST | `/api/admin/products/{id}/discount` | Назначить скидку |

## 🧪 Параметры запросов

### GET /api/products

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `page` | int | 1 | Номер страницы |
| `sortBy` | string | name | Поле сортировки (name, price) |
| `sortOrder` | string | asc | Направление (asc, desc) |
| `categoryId` | int | - | Фильтр по категории |

## 👥 Роли

### Покупатель (ROLE_USER)
- Просматривает товары
- Добавляет товары в корзину
- Оплачивает корзину
- Скачивает электронный чек

### Администратор (ROLE_ADMIN)
- Всё, что может покупатель
- Создаёт и редактирует товары
- Назначает скидки (в процентах)

## 🐳 Команды Docker

```bash
# Запуск
docker-compose up -d

# Пересборка
docker-compose up -d --build

# Остановка
docker-compose down

# Просмотр логов
docker-compose logs -f

# Зайти в контейнер
docker exec -it foodmarket-php sh
docker exec -it foodmarket-postgres psql -U foodmarket -d foodmarket

# Выполнить миграции
docker exec foodmarket-php php bin/console doctrine:migrations:migrate

# Очистить кэш
docker exec foodmarket-php php bin/console cache:clear
```

## 📖 CI/CD

В проекте настроен GitHub Actions (`.github/workflows/ci.yml`):

1. **PHP Checks** — статический анализ PHPStan
2. **Docker Build** — сборка Docker-образов
3. **Deploy** — заглушка для деплоя (можно настроить под свой сервер)

## 🎯 Цель проекта

Проект создан для изучения:
- **PHP + Symfony** — как строить REST API
- **Doctrine ORM** — работа с базой данных через объекты
- **JWT авторизация** — защита API токенами
- **Docker** — контейнеризация приложения
- **CI/CD** — автоматическая проверка кода
- **Vanilla JS** — работа с fetch API, асинхронность
- **Чистый код** — разделение ответственности, комментарии

## 📄 Лицензия

Учебный проект. Свободно использовать для обучения.
