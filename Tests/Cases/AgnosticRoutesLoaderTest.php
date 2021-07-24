<?php

namespace Prokl\WpSymfonyRouterBundle\Tests\Cases;

use Prokl\WpSymfonyRouterBundle\Services\Agnostic\RoutesLoader;
use Prokl\TestingTools\Base\BaseTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AgnosticRoutesLoaderTest
 * @package Prokl\BitrixSymfonyRouterBundle\Tests
 * @coversDefaultClass RoutesLoader
 *
 * @since 24.07.2021
 */
class AgnosticRoutesLoaderTest extends BaseTestCase
{
    /**
     * @var RoutesLoader $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @var Filesystem $filesystem Файловая система.
     */
    private $filesystem;

    /**
     * @var string $cacheDir
     */
    private $cacheDir = __DIR__ . '/../Fixture/cache';

    /**
     * @var string $routesConfig
     */
    private $routesConfig = __DIR__ . '/../Fixture/bitrix_routes.yaml';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->obTestObject = new RoutesLoader(
            $this->routesConfig,
            null,
            true
        );

    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->filesystem->exists($this->cacheDir)) {
            $this->filesystem->remove($this->cacheDir);
        }
    }

    /**
     * getRoutes().
     *
     * @return void
     */
    public function testGetRoutes() : void
    {
        $result = $this->obTestObject->getRoutes();

        $routes = $result->get('first_agnostic_route');

        $this->assertSame($routes->getPath(), '/foo/{param}/');
    }

    /**
     * Caching.
     *
     * @return void
     */
    public function testCaching() : void
    {
        $this->obTestObject = new RoutesLoader(
            $this->routesConfig,
            $this->cacheDir,
            true
        );

        $this->assertFileExists($this->cacheDir . '/route_collection.json');
        $this->assertFileExists($this->cacheDir . '/url_generating_routes.php');
        $this->assertFileExists($this->cacheDir . '/url_generating_routes.php.meta');
    }

    /**
     * purgeCache().
     *
     * @return void
     */
    public function testpurgeCache(): void
    {
        $this->obTestObject = new RoutesLoader(
            $this->routesConfig,
            $this->cacheDir,
            true
        );

        if (!$this->filesystem->exists($this->cacheDir)) {
            @mkdir($this->cacheDir);
        }

        file_put_contents($this->cacheDir . '/test', 'OK');

        $this->obTestObject->purgeCache();

        $this->assertDirectoryDoesNotExist($this->cacheDir);
    }
}
