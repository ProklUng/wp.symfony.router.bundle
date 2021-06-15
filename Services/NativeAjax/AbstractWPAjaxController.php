<?php

namespace Prokl\WpSymfonyRouterBundle\Services\NativeAjax;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractWPAjaxController
 * @package Prokl\WpSymfonyRouterBundle\Services\NativeAjax
 */
class AbstractWPAjaxController extends AbstractController
{
    /**
     * @var Request $request Request.
     */
    protected $request;

    /**
     * AbstractWPAjaxController constructor.
     *
     * @param Request|null $request Request.
     */
    public function __construct(?Request $request = null)
    {
        $this->request = $request;
        if ($request === null) {
            $this->request = Request::createFromGlobals();
        }

        $action = $this->request->query->get('action');
        if ($action) {
            $routeData = WpAjaxInitializer::route($action);

            $this->request->attributes->set('methods', $routeData->getMethods());
            $this->request->attributes->set('requirements', $routeData->getRequirements());
            $this->request->attributes->set('defaults', $routeData->getDefaults());
            $this->request->attributes->set('options', $routeData->getOptions());
        }
    }

    /**
     * Валидный тип запроса?
     *
     * @param string  $message    Сообщение.
     * @param integer $statusCode HTTP status code.
     *
     * @return void
     */
    protected function checkTypeRequest(string $message = '', int $statusCode = 400) : void
    {
        $methods = $this->request->attributes->get('methods');
        if (!$methods) {
            return;
        }

        $requestMethod = $this->request->getMethod();
        if (!in_array($requestMethod, $methods, true)) {
            $response = new Response(
                $message,
                $statusCode
            );

            $response->send();

            wp_die();
        }
    }
}
