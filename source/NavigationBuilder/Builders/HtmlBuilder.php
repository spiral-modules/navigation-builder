<?php

namespace Spiral\NavigationBuilder\Builders;

use Spiral\NavigationBuilder\Database\Sources\TreeSource;
use Spiral\NavigationBuilder\DefaultRenderer;
use Spiral\NavigationBuilder\RendererInterface;

class HtmlBuilder extends StructureBuilder
{
    /** @var RendererInterface */
    private $renderer;

    /**
     * HtmlBuilder constructor.
     *
     * @param TreeSource      $source
     * @param DefaultRenderer $renderer
     */
    public function __construct(TreeSource $source, DefaultRenderer $renderer)
    {
        parent::__construct($source);

        $this->renderer = $renderer;
    }

    /**
     * @param RendererInterface $renderer
     * @return HtmlBuilder
     */
    public function withRenderer(RendererInterface $renderer): HtmlBuilder
    {
        $builder = clone $this;
        $builder->renderer = $renderer;

        return $builder;
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