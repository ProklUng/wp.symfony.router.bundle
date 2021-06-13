<?php

namespace Prokl\WpSymfonyRouterBundle\Services\NativeAjax;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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
    }
}