<?php

namespace Prokl\WpSymfonyRouterBundle\Tests\Cases;

use Prokl\WordpressCi\Base\WordpressableAjaxTestCase;

/**
 * Class SampleAjaxControllerTest
 * @package Fedy\Services\Wordpress
 */
class SampleAjaxControllerTest extends WordpressableAjaxTestCase
{
    protected function setUp() : void
    {
        parent::setup();
        wp_set_current_user( 1 );
    }

    public function testAction() : void
    {
        $this->_handleAjax('examples_wp');
        $result = $this->_last_response;
        $this->assertSame('OK', $result);
    }
}