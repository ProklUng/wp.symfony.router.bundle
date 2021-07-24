<?php

namespace Prokl\WpSymfonyRouterBundle\Tests\Fixture;

/**
 * Class ExampleBitrixActionController
 * @package Prokl\WpSymfonyRouterBundle\Tests\Fixture
 *
 * @since 24.07.2021
 */
class ExampleAgnosticController
{
    /**
     * @return string
     */
    public static function getControllerClass() {
        return ExampleAgnosticController::class;
    }

    /**
     * @return string
     */
    public static function getDefaultName() {
        return 'testingAction';
    }

    public function cacheAction(string $country)
    {
        return ['cacheDir' => 'test', 'country' => $country];
    }

    public function configureActions()
    {
        return [
            'cache' => [
                'prefilters' => [], 'postfilters' => [],
            ],
        ];
    }
}