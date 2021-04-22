<?php

namespace Prokl\WpSymfonyRouterBundle\Services\Utils;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ClearRouteCacheService
 * @package Prokl\WpSymfonyRouterBundle\Services\Utils
 *
 * @since 21.03.2021
 */
class ClearRouteCacheService
{
    /**
     * @var Filesystem $fs Filesystem
     */
    private $fs;

    /**
     * @var Router $router Router
     */
    private $router;

    /**
     * @var string $cacheDir Cache dir path
     */
    private $cacheDir;

    /**
     * Constructor
     *
     * @param Filesystem $fs             Filesystem
     * @param Router     $router         Router
     * @param string     $kernelCacheDir Cache dir path
     */
    public function __construct(Filesystem $fs, Router $router, string $kernelCacheDir)
    {
        $this->fs       = $fs;
        $this->router   = $router;
        $this->cacheDir = $kernelCacheDir;
    }

    /**
     * Clear cache
     *
     * @return void
     */
    public function clearRouteCache(): void
    {
        // Delete routing cache files
        $finder = new Finder();

        /** @var File $file */
        $files = $finder->files()->depth('== 0')->in($this->cacheDir);

        foreach ($files as $file) {
            if (preg_match('/UrlGenerator|UrlMatcher/', $file->getFilename()) === 1) {
                $this->fs->remove($file->getRealPath());
            }
        }

        $this->router->warmUp($this->cacheDir);
    }
}
