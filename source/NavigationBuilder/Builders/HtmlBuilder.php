<?php

namespace Spiral\NavigationBuilder\Builders;

use Spiral\NavigationBuilder\RendererInterface;

class HtmlBuilder extends StructureBuilder
{
    /** @var RendererInterface */
    private $renderer;

    /**
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param string $domain
     * @return string
     */
    public function build(string $domain)
    {
        $navigation = parent::build($domain);

        return $this->renderer->navigation($navigation);
    }
}