<?php

namespace Prokl\WpSymfonyRouterBundle\Tests\Fixture\Controller;

use Prokl\WpSymfonyRouterBundle\Services\NativeAjax\AbstractWPAjaxController;

/**
 * Class SampleAjaxController
 * @package Prokl\WpSymfonyRouterBundle\Tests\Fixture\Controller
 */
class SampleAjaxController extends AbstractWPAjaxController
{
    public function action()
    {
        echo 'OK';
        wp_die();
    }
}