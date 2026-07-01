<?php

/**
 * FoodMarket - точка входа в приложение
 * 
 * Все HTTP-запросы проходят через этот файл.
 * Он загружает автозагрузчик Composer и запускает Symfony.
 */

use App\Kernel;

// загружаем автозагрузчик Composer
require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
