<?php

namespace Prokl\WpSymfonyRouterBundle\Tests\Cases;

use Faker\Factory;
use Prokl\WpSymfonyRouterBundle\Services\Utils\RouteChecker;
use Prokl\WpSymfonyRouterBundle\Tests\Fixture\ExampleSimpleController;
use Prokl\WpSymfonyRouterBundle\Tests\Tools\ContainerAwareBaseTestCase;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RoutingTest
 * @package Prokl\WpSymfonyRouterBundle\Tests\Cases
 * @coversDefaultClass RouteChecker
 *
 * @since 01.12.2020
 * @since 24.12.2020 Актуализация.
 */
class RoutingTest extends ContainerAwareBaseTestCase
{
    private const TEST_ROUTE = '/api/testing/';

    /**
     * @var RouteChecker $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->obTestObject = new RouteChecker(
            $this->getRouteCollection(),
            $this->container->get('global.request'),
            new RequestContext(
                '',
                'GET',
                $this->container->getParameter('local.http.host')
            )
        );
    }

    /**
    * isLiveRoute().
    *
    * @return void
    */
    public function testIsLiveRoute() : void
    {
        $result = $this->obTestObject->isLiveRoute(self::TEST_ROUTE);

        $this->assertTrue(
            $result,
            'Не определился существующий роут.'
        );

        $result = $this->obTestObject->isLiveRoute('/fake/route');

        $this->assertFalse(
            $result,
            'Не определился фейковый роут.'
        );
    }

    /**
     * getRouteInfo().
     *
     * @return void
     */
    public function testGetRouteInfo() : void
    {
        $result = $this->obTestObject->getRouteInfo(self::TEST_ROUTE);

        $this->assertNotEmpty(
            $result,
            'Не получили информацию на существующий роут.'
        );

        $this->assertSame(
            ExampleSimpleController::class,
            $result['_controller'],
            'Не верная информация в ответе.'
        );

        $this->assertSame(
            'foo-test',
            $result['_route'],
            'Не верная информация в ответе.'
        );

        $result = $this->obTestObject->isLiveRoute('/fake/route');

        $this->assertEmpty(
            $result,
            'Не получили информацию на фейковый роут.'
        );
    }

    /**
     * generateUrl().
     *
     * @return void
     */
    public function testGenerateUrl() : void
    {
        $result = $this->obTestObject->generateUrl(
            'foo-test',
            []
        );

        $this->assertSame(
            '/api/testing/',
            $result,
            'Не верный url роута.'
        );

        $result = $this->obTestObject->generateUrl('fake_route');

        $this->assertEmpty(
            $result,
            'Неправильно обработался фейковый роут.'
        );
    }

    /**
     * generateUrl(). Абсолютный Url.
     *
     * @return void
     */
    public function testGenerateUrlAbsoluteUrl() : void
    {
        $result = $this->obTestObject->generateUrl(
            'foo-test',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $expectedHost = 'http://' . $this->containerSymfony->getParameter('kernel.http.host');

        $this->assertSame(
            $expectedHost . '/api/testing/',
            $result,
            'Не верный url роута.'
        );

        $result = $this->obTestObject->generateUrl(
            'fake-route',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->assertEmpty(
            $result,
            'Не пустой ответ на фэйковом роуте.'
        );
    }

    /**
     * getRouteInfo().
     *
     * @return void
     */
    public function testGetRouteInfoValidPath() : void
    {
        $result = $this->obTestObject->getRouteInfoReference(
            self::TEST_ROUTE,
            ['test' => 'option']
        );

        $this->assertInstanceOf(
            ControllerReference::class,
            $result,
            'Не тот класс на валидных данных.'
        );

        $this->assertSame(
            ExampleSimpleController::class,
            $result->controller,
            'Не тот контроллер на валидных данных.'
        );

        $this->assertSame(
            'option',
            $result->attributes['test'],
            'Опции не отработались.'
        );
    }

    /**
     * getRouteInfo(). Invalid path
     *
     * @return void
     */
    public function testGetRouteInfoInvalidPath() : void
    {
        $result = $this->obTestObject->getRouteInfoReference(
            $this->faker->url,
            ['test' => 'option']
        );

        $this->assertNull(
            $result,
            'Не пустой ответ на фэйковых данных.'
        );
    }

    /**
     * Тестовая коллекция роутов.
     *
     * @return RouteCollection
     */
    private function getRouteCollection() : RouteCollection
    {
        $route = new Route(
            self::TEST_ROUTE,
            ['_controller' => ExampleSimpleController::class, 'id' => $this->faker->numberBetween(100, 200)]
        );

        $route->setHost($this->container->getParameter('kernel.http.host'));

        $routeCollection = new RouteCollection();

        $routeCollection->add(
            'foo-test',
            $route
        );

        return $routeCollection;
    }
}
