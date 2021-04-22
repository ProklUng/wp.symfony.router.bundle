<?php

namespace Prokl\WpSymfonyRouterBundle\Services\Listeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StringResponseListener
 * If the response is a string, convert it to a proper Response object.
 * @package Prokl\WpSymfonyRouterBundle\Services\Listeners
 */
class StringResponseListener implements EventSubscriberInterface
{
    /**
     * if the response is a string, convert it to a proper Response object
     *
     * @param ViewEvent $event
     */
    public function onView(ViewEvent $event) : void
    {
        $response = $event->getControllerResult();

        if (is_string($response)) {
            $event->setResponse(new Response($response));
        }
    }

    /**
     * Подписчик на событие.
     *
     * @return string[]
     */
    public static function getSubscribedEvents() : array
    {
        return ['kernel.view' => 'onView'];
    }
}
