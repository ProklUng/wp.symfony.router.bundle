<?php

namespace Prokl\WpSymfonyRouterBundle\Services\Router;

use Exception;
use Prokl\WpSymfonyRouterBundle\Services\Interfaces\ErrorControllerInterface;
use Prokl\WpSymfonyRouterBundle\Services\Listeners\StringResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;

/**
 * Class InitRouter
 * @package Prokl\WpSymfonyRouterBundle\Services\Router
 *
 * @since 05.09.2020 Refactoring.
 * @since 08.09.2020 Вынес ErrorListener & ErrorController в свойства.
 * @since 10.09.2020 HttpKernelSubscribersBag. Подписчики событий HttpKernel.
 * @since 11.09.2020 Удален HttpKernelSubscribersBag. Все через конфиг.
 * @since 12.09.2020 Вынес инициализацию хуков в публичный метод, вызывающийся из сервис-контейнера.
 * @since 26.09.2020 Трэйт Eventable. Инициализация по тэгу в сервис-контейнере.
 * @since 28.09.2020 ControllerResolver & ArgumentResolverInterface пробрасываются снаружи.
 * @since 19.11.2020 RequestStack пробрасывается снаружи.
 * @since 02.02.2021 Очистка контекста - лишние классы.
 * @since 24.02.2021 Роуты бандлов.
 * @since 06.03.2021 Инициация события kernel.terminate.
 * @since 21.03.2021 URL matcher опционально пробрасывается снаружи.
 */
class InitRouter
{
    /**
     * @var array $bundlesRoutes Роуты бандлов.
     */
    private static $bundlesRoutes = [];

    /**
     * @var RouteCollection $routeCollection Коллекция роутов.
     */
    private $routeCollection;

    /**
     * @var ErrorListener $errorListener Error listener.
     */
    private $errorListener;

    /**
     * @var Request $request Request приложения.
     */
    private $request;

    /**
     * @var RouterListener $routeListener Слушатель роутов.
     */
    private $routeListener;

    /**
     * @var EventDispatcherInterface $dispatcher Диспетчер событий.
     */
    private $dispatcher;

    /**
     * @var ControllerResolverInterface $resolver Ресолвер контроллеров.
     */
    private $resolver;

    /**
     * @var ArgumentResolverInterface $argumentResolver Argument Resolver.
     */
    private $argumentResolver;

    /**
     * @var RequestStack $requestStack RequestStack.
     */
    private $requestStack;

    /**
     * InitRouter constructor.
     *
     * @since 06.09.2020 Инициализация зависимостей.
     * @since 09.09.2020 ErrorController как зависимость снаружи.
     * @since 10.09.2020 HttpKernelSubscribersBag. Подписчики событий HttpKernel.
     * @since 16.09.2020 Доработка. RequestContext.
     * @since 26.09.2020 HttpKernelSubscribersBag. Подписчики событий HttpKernel.
     * @since 28.09.2020 ControllerResolver & ArgumentResolverInterface пробрасываются снаружи.
     * @since 19.11.2020 RequestStack пробрасывается снаружи.
     * @since 21.03.2021 URL matcher опционально пробрасывается снаружи.
     *
     * @param RouteCollection             $routeCollection    Коллекция роутов.
     * @param ErrorControllerInterface    $errorController    Error controller.
     * @param EventDispatcherInterface    $eventDispatcher    Event dispatcher.
     * @param ControllerResolverInterface $controllerResolver Controller resolver.
     * @param ArgumentResolverInterface   $argumentResolver   Argument resolver.
     * @param Request                     $request            Request приложения.
     * @param RequestStack                $requestStack       Request stack.
     * @param UrlMatcherInterface|null    $urlMatcher         URL matcher.
     */
    public function __construct(
        RouteCollection $routeCollection,
        ErrorControllerInterface $errorController,
        EventDispatcherInterface $eventDispatcher,
        ControllerResolverInterface $controllerResolver,
        ArgumentResolverInterface $argumentResolver,
        Request $request,
        RequestStack $requestStack,
        UrlMatcherInterface $urlMatcher = null
    ) {
        $this->request = $request;
        $this->resolver = $controllerResolver;
        $this->argumentResolver = $argumentResolver;
        $this->routeCollection = $routeCollection;

        $this->requestStack = $requestStack;
        $this->requestStack->push($request);

        // RequestContext init.
        $requestContext = new RequestContext();
        $requestContext->fromRequest($this->request);

        // Роуты бандлов.
        $this->mixRoutesBundles();

        // Инициализация необходимого для запуска роутера.
        $matcher = $urlMatcher ?? new UrlMatcher(
            $this->routeCollection,
            $requestContext
        );

        $matcher->setContext($requestContext);

        $this->dispatcher = $eventDispatcher;
        $this->routeListener = new RouterListener(
            $matcher, $this->requestStack
        );

        $this->errorListener = new ErrorListener(
            [$errorController, 'exceptionAction']
        );
    }

    /**
     * Инициализация событий Wordpress.
     *
     * @return void
     */
    public function addEvent(): void
    {
        add_action('template_redirect', [$this, 'router'], 5);
    }

    /**
     * Инициализация роутера.
     *
     * @return void
     */
    public function router(): void
    {
        // Setup dispatcher and add route listener
        $this->initEventDispatcher();

        // Setup framework kernel
        $framework = new HttpKernel(
            $this->dispatcher,
            $this->resolver,
            $this->requestStack,
            $this->argumentResolver
        );

        // Handle response
        try {
            $response = $framework->handle($this->request);
            // Инициирует событие kernel.terminate.
            $framework->terminate($this->request, $response);
        } catch (Exception $e) {
            return;
        }

        // Handle if no route match found
        if ($response->getStatusCode() === 404) {
            // If no route found do noting and let wp continue.
            return;
        }

        // Bugfix. Статические страницы почему-то грузятся по два раза.
        if ((bool)$response->headers->get('static-page')) {
            die();
        }

        // Send the response to the browser and exit app.
        $response->send();

        exit;
    }

    /**
     * Подмес роутов бандлов к общим роутам.
     *
     * @return void
     */
    public function mixRoutesBundles() : void
    {
        if (!static::$bundlesRoutes) {
            return;
        }

        foreach (static::$bundlesRoutes as $collection) {
            if ($collection instanceof RouteCollection) {
                $this->routeCollection->addCollection($collection);
            }
        }
    }

    /**
     * Добавить роуты бандлов.
     *
     * @param RouteCollection $routeCollection Коллкция роутов.
     *
     * @return void
     */
    public static function addRoutesBundle(RouteCollection $routeCollection) : void
    {
        static::$bundlesRoutes[] = $routeCollection;
    }

    /**
     * Инициализировать диспетчер событий.
     *
     * @since 06.09.2020
     *
     * @return void
     */
    private function initEventDispatcher() : void
    {
        // Необходимые слушатели событий.
        $arSubscribers = [
            $this->routeListener,
            new StringResponseListener(),
            $this->errorListener,
            new ResponseListener('UTF-8')
        ];

        $this->addSubscribers($arSubscribers);
    }

    /**
     * Кучно добавить слушателей событий.
     *
     * @param array $subscribers Подписчики.
     *
     * @return void
     *
     * @since 06.09.2020
     */
    private function addSubscribers(array $subscribers = []) : void
    {
        foreach ($subscribers as $subscriber) {
            if (!is_object($subscriber)) {
                continue;
            }

            /**
             * @var EventSubscriberInterface $subscriber
             */
            $this->dispatcher->addSubscriber($subscriber);
        }
    }
}
