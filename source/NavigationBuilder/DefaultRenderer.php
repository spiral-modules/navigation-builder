<?php

namespace Spiral\NavigationBuilder;

use Spiral\Core\Service;
use Spiral\Views\ViewManager;

class DefaultRenderer extends Service implements RendererInterface
{
    /** @var ViewManager */
    protected $views;

    /** @var NavigationBuilderConfig */
    protected $config;

    /**
     * Renderer constructor.
     *
     * @param ViewManager             $views
     * @param NavigationBuilderConfig $config
     */
    public function __construct(ViewManager $views, NavigationBuilderConfig $config)
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
        $output = [];
        foreach ($navigation as $item) {
            $output[] = $this->link($item['link']) . $this->navigation($item['sub']);
        }

        return $this->views->render($this->config->treeView(), ['navigation' => $output]);
    }
}