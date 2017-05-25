<?php

namespace Spiral\NavigationBuilder;

use Spiral\Core\Service;
use Spiral\NavigationBuilder\Database\Link;
use Spiral\Views\ViewManager;

class DefaultRenderer extends Service implements RendererInterface
{
    /** @var ViewManager */
    private $views;

    /** @var Config */
    private $config;

    /**
     * Renderer constructor.
     *
     * @param ViewManager $views
     * @param Config      $config
     */
    public function __construct(ViewManager $views, Config $config)
    {
        $this->views = $views;
        $this->config = $config;
    }

    /**
     * Render single link.
     *
     * @param array $link
     * @return string
     */
    public function link(array $link): string
    {
        return $this->views->render($this->config->linkView(), compact('link'));
    }

    /**
     * Render full navigation.
     *
     * @param array $navigation
     * @return string
     */
    public function navigation(array $navigation): string
    {
        return $this->views->render($this->config->navigationView(), compact('navigation'));
    }
}