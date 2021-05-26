<?php

namespace Prokl\WpSymfonyRouterBundle\Tests\Cases;

use Exception;
use Prokl\WpSymfonyRouterBundle\Services\Controllers\ErrorJsonController;
use Prokl\WpSymfonyRouterBundle\Tests\Tools\ContainerAwareBaseTestCase;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

/**
 * Class ErrorJsonControllerTest
 * @package Prokl\WpSymfonyRouterBundle\Tests
 * @coversDefaultClass ErrorJsonController
 *
 * @since 09.09.2020
 */
class ErrorJsonControllerTest extends ContainerAwareBaseTestCase
{
    /**
     * @var ErrorJsonController $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = new ErrorJsonController(
            $this->container->get('serializer')
        );
    }

    /**
    * exceptionAction().
    */
    public function testExceptionAction() : void
    {
        $exception = FlattenException::create(
            new Exception('test', 400)
        );
        $exception->setStatusCode(400);

        $result = $this->obTestObject->exceptionAction(
            $exception
        );

        $this->assertSame(
            '{"error":true,"message":"test"}',
            $result->getContent(),
            'Ответ не правильный.'
        );

        $this->assertSame(
            400,
            $result->getStatusCode(),
            'HTTP код не правильный.'
        );
    }
}
