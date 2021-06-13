<?php

namespace Prokl\WpSymfonyRouterBundle\Tests\Fixture\Controller;

use Prokl\WpSymfonyRouterBundle\Services\NativeAjax\AbstractWPAjaxController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SampleAjaxController
 * @package Prokl\WpSymfonyRouterBundle\Tests\Fixture\Controller
 */
class SampleAjaxController extends AbstractWPAjaxController
{
    /**
     * @return void
     */
    public function action() : void
    {
        $this->checkTypeRequest('Invalid type request');

        $response = new Response(
            'OK',
            Response::HTTP_OK
        );

        $response->headers->set('Content-Type', 'application/html; charset=utf-8');
        $response->send();

        wp_die();
    }
}