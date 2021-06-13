<?php

namespace Prokl\WpSymfonyRouterBundle\Services\NativeAjax;

use LogicException;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class Prokl\WpSymfonyRouterBundle\Services\NativeAjax
 * @package Fedy\Services\Wordpress
 *
 * @since 12.06.2021
 */
class WpAjaxInitializer
{
    /**
     * @var RouteCollection $routeCollection Роуты.
     */
    private $routeCollection;

    /**
     * @var Route[] $routes Данные на роуты.
     */
    private static $routes;

    /**
     * @var ContainerInterface $container Контейнер.
     */
    private $container;

    /**
     * WpAjaxInitializer constructor.
     *
     * @param RouteCollection    $routes    Роуты.
     * @param ContainerInterface $container Контейнер.
     */
    public function __construct(RouteCollection $routes, ContainerInterface $container)
    {
        $this->routeCollection = $routes;
        static::$routes = $this->routeCollection->all();
        $this->container = $container;

        $this->init();
    }

    /**
     * Данные на роут.
     *
     * @param string $action Action.
     *
     * @return Route
     * @throws LogicException Когда роут не найден.
     */
    public function getRouteData(string $action) : Route
    {
        if (array_key_exists($action, static::$routes)) {
            return static::$routes[$action];
        }

        throw new LogicException('Route ' . $action . ' not found.');
    }

    /**
     * Данные на роут. Статический фасад.
     *
     * @param string $action Action.
     *
     * @return Route
     * @throws LogicException Когда роут не найден.
     */
    public static function route(string $action) : Route
    {
        if (array_key_exists($action, static::$routes)) {
            return static::$routes[$action];
        }

        throw new LogicException('Route ' . $action . ' not found.');
    }

    /**
     * Инициализация.
     *
     * @return void
     */
    private function init() : void
    {
        foreach (static::$routes as $action => $route) {
            $defaults = $route->getDefaults();
            // Публичный роут или нет.
            $public = $defaults['_public'] ?? false;

            $controller = $this->parseController($defaults['_controller']);

            add_action("wp_ajax_{$action}", $controller);
            if ($public) {
                add_action("wp_ajax_nopriv_{$action}", $controller);
            }
        }
    }

    /**
     * Распарсить контроллер.
     *
     * @param array|string|object $controller Данные из конфигурационного файла.
     *
     * @return array|false|object|string|string[]|null
     * @throws RuntimeException Когда не получилось распарсить контроллер.
     */
    private function parseController($controller)
    {
        if (is_string($controller)) {
            if (strpos($controller, '::') !== false) {
                $controller = explode('::', $controller, 2);
            } else {
                // Invoked controller.
                try {
                    new ReflectionMethod($controller, '__invoke');
                    $controller = [$controller, '__invoke'];
                } catch (ReflectionException $e) {
                    return [];
                }
            }
        }

        if (is_array($controller)) {
            if (is_string($controller[0]) && !$this->container->has($controller[0])) {
                throw new RuntimeException(
                    sprintf(
                        'Controller %s not found in container. Forgot mark him as service?',
                        $controller[0]
                    ),
                );
            }

            $controller[0] = $this->container->get($controller[0]);

            return $controller;
        }

        if (is_object($controller)) {
            return [$controller];
        }

        throw new RuntimeException('Error parsing controller');
    }
}
