<?php

namespace Prokl\WpSymfonyRouterBundle\Tests\Cases;

use Prokl\TestingTools\Tools\Container\BuildContainer;
use Prokl\WordpressCi\Base\WordpressableTestCase;
use Prokl\WpSymfonyRouterBundle\Services\NativeAjax\WpAjaxInitializer;
use RuntimeException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class WpAjaxInitializerTest
 * @package Fedy\Services\Wordpress
 */
class WpAjaxInitializerTest extends WordpressableTestCase
{
    /**
     * @var WpAjaxInitializer $obTestObject
     */
    protected $obTestObject;

    /**
     * @var RouteCollection $routeCollection
     */
    private $routeCollection;

    /**
     * @var string $action
     */
    private $action = 'test_action';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = static::$testContainer = BuildContainer::getTestContainer(
            [
                'test_container.yaml'
            ],
            '/../../../../Tests/Fixture'
        );

        $this->routeCollection = new RouteCollection();
        $this->routeCollection->add(
            $this->action,
            $this->getRoute()
        );

    }

    /**
     * Инициализация.
     *
     * @return void
     */
    public function testInit() : void
    {
        $this->obTestObject = new WpAjaxInitializer($this->routeCollection, $this->container);

        $result = has_action('wp_ajax_' . $this->action);
        $this->assertTrue($result, 'Событие ajax не зарегистрировалось.');

        $result = has_action('wp_ajax_nopriv_' . $this->action);
        $this->assertTrue($result, 'Событие ajax для публичного доступа не зарегистрировалось.');
    }

    /**
     * Инициализация. Invoke контроллер.
     *
     * @return void
     */
    public function testInitInvoke() : void
    {
        $this->routeCollection = new RouteCollection();
        $this->routeCollection->add(
            $this->action,
            $this->getRoute(true, 'Prokl\WpSymfonyRouterBundle\Tests\Fixture\FixtureAjaxControllerInvoke')
        );

        $this->obTestObject = new WpAjaxInitializer($this->routeCollection, $this->container);

        $result = has_action('wp_ajax_' . $this->action);
        $this->assertTrue($result, 'Событие ajax не зарегистрировалось.');

        $result = has_action('wp_ajax_nopriv_' . $this->action);
        $this->assertTrue($result, 'Событие ajax для публичного доступа не зарегистрировалось.');
    }

    /**
     * Инициализация. Только для админов.
     *
     * @return void
     */
    public function testInitOnlyAdmin() : void
    {
        $this->routeCollection = new RouteCollection();
        $this->routeCollection->add(
            $this->action,
            $this->getRoute(false)
        );

        $this->obTestObject = new WpAjaxInitializer($this->routeCollection, $this->container);

        $result = has_action('wp_ajax_' . $this->action);
        $this->assertTrue($result, 'Событие ajax не зарегистрировалось.');

        $result = has_action('wp_ajax_nopriv_' . $this->action);
        $this->assertFalse($result, 'Событие ajax для публичного доступа зарегистрировалось, а не должно.');
    }

    /**
     * Инициализация. Забыли зарегистрировать контроллер сервисом.
     *
     * @return void
     */
    public function testInitNotServiceController() : void
    {
        $this->routeCollection = new RouteCollection();
        $this->routeCollection->add(
            $this->action,
            $this->getRoute(false, 'Prokl\WpSymfonyRouterBundle\Tests\Fixture\TrivialController::action')
        );

        $this->expectException(RuntimeException::class);

        $this->obTestObject = new WpAjaxInitializer($this->routeCollection, $this->container);
    }

    /**
     * @param boolean $public
     * @param string  $handler
     *
     * @return Route
     */
    private function getRoute(
        bool $public = true,
        string $handler = 'Prokl\WpSymfonyRouterBundle\Tests\Fixture\FixtureAjaxController::action'
    ): Route {
        return new Route(
            '',
            [
                '_public' => $public,
                '_controller' => $handler,
            ],
            [],
            [],
            null,
            ['POST', 'GET']
        );
    }
}