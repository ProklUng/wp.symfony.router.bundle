<?php

namespace Prokl\WpSymfonyRouterBundle\Tests\Cases;

use Prokl\TestingTools\Tools\Container\BuildContainer;
use Prokl\WordpressCi\Base\WordpressableAjaxTestCase;

/**
 * Class SampleAjaxControllerTest
 * @package Fedy\Services\Wordpress
 */
class SampleAjaxControllerTest extends WordpressableAjaxTestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setup();
        wp_set_current_user( 1 );

        $this->container = static::$testContainer = BuildContainer::getTestContainer(
            [
                'test_container.yaml'
            ],
            '/../../../../Tests/Fixture'
        );

        $this->container->get('wp_ajax.initializer');
    }

    /**
     * @return void
     */
    public function testAction() : void
    {
        $this->_handleAjax('examples_wp');
        $result = $this->_last_response;
        $this->assertSame('OK', $result);
    }

    /**
     * @return void
     */
    public function testActionInvalid() : void
    {
        $this->_handleAjax('examples_invalid');
        $result = $this->_last_response;
        $this->assertSame('', $result);
    }
}