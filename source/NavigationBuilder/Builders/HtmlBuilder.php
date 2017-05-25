<?php

namespace Spiral\NavigationBuilder\Builders;

use Spiral\NavigationBuilder\Database\Domain;
use Spiral\NavigationBuilder\Database\Link;
use Spiral\NavigationBuilder\Database\Tree;
use Spiral\NavigationBuilder\DefaultRenderer;
use Spiral\NavigationBuilder\RendererInterface;
use Spiral\NavigationBuilder\Services\LinkWrapper;

class HtmlBuilder extends TreeBuilder
{
    /** @var DefaultRenderer */
    private $renderer;

    /**
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param Domain $domain
     * @return string
     */
    public function build(Domain $domain)
    {
        $map = parent::build($domain);

        return $this->renderer->navigation($map);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function packRow(array $data): array
    {
        return parent::packRow($data) + [
            'depth'      => $data['depth'],
            'text'       => $data['link']['text'],
            'href'       => $data['link']['href'],
            'attributes' => LinkWrapper::unpackAttributes($data['link']['attributes']),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    protected function packLink(array $data): array
    {
        return parent::packLink($data) + [
            'depth'      => $data['depth'],
            'text'       => $data['text'],
            'href'       => $data['href'],
            'attributes' => $data['attributes'],
        ];
    }

    /**
     * @param Domain $domain
     * @return array
     */
    protected function getTree(Domain $domain): array
    {
        return $this->source->findDomainTree($domain)->orderBy('order', 'ASC')->fetchData();
    }
}