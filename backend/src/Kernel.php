<?php

/**
 * FoodMarket - Kernel
 * 
 * Главный класс ядра Symfony-приложения.
 * Связывает все компоненты и конфигурации вместе.
 */

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
