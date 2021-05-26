<?php

namespace Prokl\WpSymfonyRouterBundle\Services\Utils;

use Exception;
use Prokl\WpSymfonyRouterBundle\Services\Controllers\ErrorJsonController;
use Prokl\WpSymfonyRouterBundle\Services\Listeners\StringResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;

/**
 * Class DispatchController
 * @package Prokl\WpSymfonyRouterBundle\Services\Utils
 *
 * @since 05.09.2020
 * @since 07.09.2020 Light rewriting.
 * @since 11.09.2020 Доработки.
 * @since 21.10.2020 Доработки. Сеттеры и геттеры. Заголовки.
 * @since 24.10.2020 ErrorJsonController прибывает снаружи.
 * @since 31.10.2020 ArgumentResolverInterface пробрасывается снаружи.
 * @since 04.11.2020 Сеттер POST параметров.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DispatchController
{
    /**
     * @var Request $request Request.
     */
    private $request;

    /**
     * @var Response|null $response Response.
     */
    private $response;

    /**
     * @var EventDispatcherInterface $dispatcher Диспетчер событий.
     */
    private $dispatcher;

    /**
     * @var ControllerResolverInterface $controllerResolver Разрешитель контроллеров.
     */
    private $controllerResolver;

    /**
     * @var ArgumentResolverInterface $argumentResolver Argument Resolver.
     */
    private $argumentResolver;

    /** @var array $defaultSubscribers Подписчики на события по умолчанию. */
    private $defaultSubscribers;

    /** @var array $headers Заголовки запроса. */
    private $headers = [];

    /**
     * DispatchController constructor.
     *
     * @param EventDispatcherInterface    $dispatcher          Диспетчер событий.
     * @param ControllerResolverInterface $controllerResolver  Разрешитель контроллеров.
     * @param ArgumentResolverInterface   $argumentResolver    Argument resolver.
     * @param ErrorJsonController         $errorJsonController Ошибки в JSON.
     * @param Request|null                $request             Request.
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        ControllerResolverInterface $controllerResolver,
        ArgumentResolverInterface $argumentResolver,
        ErrorJsonController $errorJsonController,
        Request $request = null
    ) {
        $this->dispatcher = $dispatcher;

        $this->controllerResolver = $controllerResolver;
        $this->argumentResolver = $argumentResolver;

        $this->request = $request ?? Request::createFromGlobals();

        // Подписчики на события по умолчанию.
        $this->defaultSubscribers = [
            new StringResponseListener(),
            new ErrorListener(
                [$errorJsonController, 'exceptionAction']
            ),
            new ResponseListener('UTF-8')
        ];
    }

    /**
     * Исполнить контроллер.
     *
     * @param string|array $controllerAction Класс и метод контроллера.
     * Вида \Local\Handler::action. Или массив [класс, метод].
     *
     * @return boolean
     *
     * @since 06.09.2020 Small rewrite. Массив в качестве параметра.
     */
    public function dispatch(
        $controllerAction
    ): bool {
        // Задать контроллер
        $this->request->attributes->set(
            '_controller',
            $controllerAction
        );

        $this->request->headers->add($this->headers);

        $this->addSubscribers($this->defaultSubscribers);

        $framework = new HttpKernel(
            $this->dispatcher,
            $this->controllerResolver,
            null,
            $this->argumentResolver
        );

        try {
            $this->response = $framework->handle($this->request);
            $framework->terminate($this->request, $this->response);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Заслать Response в браузер.
     *
     * @return boolean
     */
    public function send(): bool
    {
        // Чтобы не вызывался этот метод ранее dispatch.
        if ($this->response !== null) {
            $this->response->send();
            return false;
        }

        return true;
    }

    /**
     * Задать Request.
     *
     * @param Request $request Request.
     *
     * @return DispatchController
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Задать $_GET параметры.
     *
     * @param array $query Query параметры.
     *
     * @return $this
     *
     * @since 21.10.2020
     */
    public function setQuery(array $query) : self
    {
        $this->request->query->add($query);

        return $this;
    }

    /**
     * Задать заголовки запроса.
     *
     * @param array $headers Заголовки.
     *
     * @return $this
     *
     * @since 21.10.2020
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Задать параметры Request.
     *
     * @param array $arParams Параметры (лягут в аттрибуты Request).
     *
     * @return DispatchController
     */
    public function setParams(array $arParams): self
    {
        $this->request->attributes->add($arParams);

        return $this;
    }

    /**
     * @param array $post POST параметры.
     *
     * @return DispatchController
     *
     * @since 04.11.2020
     */
    public function setPost(array $post): self
    {
        $this->request->request->add($post);

        return $this;
    }

    /**
     * Задать дополнительного подписчика на события.
     *
     * @param mixed $listener
     *
     * @return $this
     *
     * @since 07.09.2020
     */
    public function addListener($listener) : self
    {
        if (is_object($listener)) {
            $this->defaultSubscribers[] = $listener;
        }

        return $this;
    }

    /**
     * Получить Response.
     *
     * @return Response|null
     *
     * @since 21.10.2020
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Кучно добавить слушателей событий.
     *
     * @param array $subscribers
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
