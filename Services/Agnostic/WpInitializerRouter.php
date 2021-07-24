<?php

namespace Prokl\WpSymfonyRouterBundle\Services\Agnostic;

use Prokl\WpSymfonyRouterBundle\Services\Agnostic\Contracts\RouterInitializerInterface;
use Prokl\WpSymfonyRouterBundle\Services\Router\InitRouter;

/**
 * Class WpInitializerRouter
 * @package Prokl\WpSymfonyRouterBundle\Services\Agnostic
 *
 * @since 24.07.2021
 */
class WpInitializerRouter implements RouterInitializerInterface
{
    /**
     * @inheritDoc
     */
    public function init(InitRouter $router)
    {
        add_action('template_redirect', [$router, 'router'], 5);
    }
}