<?php

namespace Spiral\NavigationBuilder\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\NavigationBuilder\DefaultRenderer;
use Spiral\NavigationBuilder\Navigation;
use Spiral\NavigationBuilder\RendererInterface;

class NavigationBootloader extends Bootloader
{
    const BOOT = true;

    /**
     * @var array
     */
    const SINGLETONS = [
        RendererInterface::class => DefaultRenderer::class,
        'navigation'             => Navigation::class,
        'nav.default'            => [self::class, 'default']
    ];

    /**
     * Example how custom renderer can be set.
     * Pass renderer and set it to navigation.
     * If you're ok with default renderer, then you can use 'navigation' binding.
     *
     * @param Navigation      $navigation
     * @param DefaultRenderer $renderer
     * @return Navigation
     */
    public function default(Navigation $navigation, DefaultRenderer $renderer)
    {
        $navigation->setRenderer($renderer);

        return $navigation;
    }
}