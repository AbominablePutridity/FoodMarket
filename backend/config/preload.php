<?php

/**
 * FoodMarket - Preload (PHP 8.1+)
 * 
 * Опциональная оптимизация производительности.
 * Загружает код в память до обработки запросов.
 */

if (file_exists(dirname(__DIR__) . '/var/cache/prod/App_KernelProdContainer.preload.php')) {
    require dirname(__DIR__) . '/var/cache/prod/App_KernelProdContainer.preload.php';
}
