<?php

namespace Prokl\WpSymfonyRouterBundle\Services\Controllers;

use Prokl\WpSymfonyRouterBundle\Services\Interfaces\ErrorControllerInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ErrorJsonController
 * @package Prokl\WpSymfonyRouterBundle\Services\Controllers
 *
 * @since 08.09.2020
 * @since 09.11.2020 Small fix.
 */
class ErrorJsonController implements ErrorControllerInterface
{
    /**
     * @var SerializerInterface $serializer Сериализатор.
     */
    private $serializer;

    /**
     * ErrorJsonController constructor.
     *
     * @param SerializerInterface $serializer Сериализатор.
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Обработчик ошибок. На выходе JSON.
     *
     * @param FlattenException $exception
     *
     * @return Response
     */
    public function exceptionAction(FlattenException $exception): Response
    {
        $arResult = [
          'error' => true,
          'message' => $exception->getMessage()
        ];

        return new Response(
            $this->serializer->serialize($arResult, 'json'),
            $exception->getStatusCode(),
            ['Content-Type' => 'application/json; charset=utf-8']
        );
    }
}
