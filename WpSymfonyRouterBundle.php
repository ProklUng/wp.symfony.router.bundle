<?php

namespace Prokl\WpSymfonyRouterBundle;

use Prokl\WpSymfonyRouterBundle\DependencyInjection\WpSymfonyRouterExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class WpSymfonyRouterBundle
 * @package Prokl\WpSymfonyRouterBundle
 *
 * @since 21.04.2021
 */
class WpSymfonyRouterBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new WpSymfonyRouterExtension();
        }

        return $this->extension;
    }
}
