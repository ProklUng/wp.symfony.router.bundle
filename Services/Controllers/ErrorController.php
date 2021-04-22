<?php

namespace Prokl\WpSymfonyRouterBundle\Services\Controllers;

use Prokl\WpSymfonyRouterBundle\Services\Interfaces\ErrorControllerInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ErrorController
 * @package Prokl\WpSymfonyRouterBundle\Services\Controllers
 *
 * @since 08.09.2020 Implements ErrorControllerInterface.
 */
class ErrorController implements ErrorControllerInterface
{
    /**
     * Обработчик ошибок.
     *
     * @param FlattenException $exception
     *
     * @return Response
     */
    public function exceptionAction(FlattenException $exception): Response
    {
        $msg = 'Something went wrong! ('.$exception->getMessage().')';

        return new Response($msg, $exception->getStatusCode());
    }
}
