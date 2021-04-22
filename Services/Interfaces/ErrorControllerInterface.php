<?php

namespace Prokl\WpSymfonyRouterBundle\Services\Interfaces;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface ErrorControllerInterface
 * @package Prokl\WpSymfonyRouterBundle\Services\Interfaces
 *
 * @since 08.09.2020
 */
interface ErrorControllerInterface
{
    /**
     * Обработчик ошибок. На выходе JSON.
     *
     * @param FlattenException $exception
     *
     * @return Response
     */
    public function exceptionAction(FlattenException $exception): Response;
}
