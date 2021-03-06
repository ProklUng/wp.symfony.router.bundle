<?php

namespace Prokl\WpSymfonyRouterBundle\Services\Agnostic\Contracts;

use Prokl\WpSymfonyRouterBundle\Services\Router\InitRouter;

/**
 * Interface Prokl\WpSymfonyRouterBundle\Services\Agnostic\Contracts
 * @package Local\Bitrix
 *
 * @since 24.07.2021
 */
interface RouterInitializerInterface
{
    /**
     * Инициализация роутера.
     *
     * @param InitRouter $router Инициализированный роутер.
     *
     * @return mixed
     */
    public function init(InitRouter $router);
}