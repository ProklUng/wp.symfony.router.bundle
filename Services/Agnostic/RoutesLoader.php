<?php

namespace Prokl\WpSymfonyRouterBundle\Services\Agnostic;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\SelfCheckingResourceChecker;
use Symfony\Component\Config\ResourceCheckerConfigCache;
use Symfony\Component\Config\ResourceCheckerConfigCacheFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class RoutesLoader
 * Независимый от контейнера загрузчик роутов.
 * @package Prokl\WpSymfonyRouterBundle\Services\Agnostic
 *
 * @since 24.07.2021
 */
class RoutesLoader
{
    /**
     * @var RouterInterface $router Роутер.
     */
    private $router;

    /**
     * @var ResourceCheckerConfigCacheFactory $cacheFactory
     */
    private $cacheFactory;

    /**
     * @var SelfCheckingResourceChecker $checker
     */
    private $checker;

    /**
     * @var ResourceCheckerConfigCache $cacheFreshChecker
     */
    private $cacheFreshChecker;

    /**
     * @var string|null $cacheDir Путь к кэшу. Null -> не кэшировать.
     */
    private $cacheDir;

    /**
     * AgnosticRouteLoader constructor.
     *
     * @param string      $configFile Yaml/php/xml файл с конфигурацией роутов.
     * @param string|null $cacheDir   Путь к кэшу. Null -> не кэшировать.
     * @param boolean     $debug      Режим отладки.
     */
    public function __construct(
        string $configFile,
        ?string $cacheDir = null,
        bool $debug = true
    ) {
        $this->cacheDir = $cacheDir;

        $resolver = new LoaderResolver(
            [
                new YamlFileLoader(new FileLocator()),
                new PhpFileLoader(new FileLocator()),
                new XmlFileLoader(new FileLocator()),
            ]
        );

        $delegatingLoader = new DelegatingLoader($resolver);

        $requestContext = new RequestContext();
        $request = Request::createFromGlobals();

        $this->checker = new SelfCheckingResourceChecker();
        $this->cacheFactory = new ResourceCheckerConfigCacheFactory([$this->checker]);

        $this->router = new Router(
            $delegatingLoader,
            $configFile,
            [
                'cache_dir' => $cacheDir,
                'debug' => $debug,
                'generator_class' => 'Symfony\Component\Routing\Generator\CompiledUrlGenerator',
                'generator_dumper_class' => 'Symfony\Component\Routing\Generator\Dumper\CompiledUrlGeneratorDumper',
                'matcher_class' =>  'Symfony\Bundle\FrameworkBundle\Routing\RedirectableCompiledUrlMatcher',
                'matcher_dumper_class' => 'Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper'
            ],
            $requestContext->fromRequest($request)
        );

        if ($cacheDir) {
            $this->cacheFreshChecker = new ResourceCheckerConfigCache(
                $this->cacheDir . '/url_generating_routes.php',
                [$this->checker]
            );

            $this->warmUpCache();
        }
    }

    /**
     * Роуты.
     *
     * @return RouteCollection
     */
    public function getRoutes() : RouteCollection
    {
        if ($this->cacheDir) {
            $compiledRoutesFile = $this->cacheDir . '/route_collection.json';

            if ($this->cacheFreshChecker !== null
                &&
                $this->cacheFreshChecker->isFresh()
                &&
                @file_exists($compiledRoutesFile)) {
                $collection = file_get_contents($compiledRoutesFile);
                if ($collection) {
                    $collection = unserialize($collection);
                    return $collection;
                }
            }
        }

        return $this->router->getRouteCollection();
    }

    /**
     * Удалить кэш.
     *
     * @return void
     */
    public function purgeCache() : void
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($this->cacheDir)) {
            return;
        }

        $filesystem->remove($this->cacheDir);
    }

    /**
     * Создать (если надо), кэш.
     *
     * @return void
     */
    private function warmUpCache() : void
    {
        $this->router->setConfigCacheFactory($this->cacheFactory);

        if (!$this->cacheFreshChecker->isFresh()) {
            if (!@file_exists($this->cacheDir)) {
                @mkdir($this->cacheDir, 0777);
            }

            file_put_contents(
                $this->cacheDir . '/route_collection.json',
                serialize($this->router->getRouteCollection())
            );
        }

        $this->router->getGenerator(); // Трюк по созданию кэша.
    }
}
