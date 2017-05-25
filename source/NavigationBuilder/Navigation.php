<?php

namespace Spiral\NavigationBuilder;

use Spiral\NavigationBuilder\Database\Sources\DomainSource;

class Navigation
{
    /** @var DomainSource */
    private $source;

    /** @var DomainNavigation */
    private $builders;

    /**
     * Navigation constructor.
     *
     * @param DomainSource      $source
     * @param DomainNavigation  $domains
     * @param RendererInterface $renderer
     */
    public function __construct(
        DomainSource $source,
        DomainNavigation $domains,
        RendererInterface $renderer
    ) {
        $this->source = $source;
        $this->builders = $domains;
        $this->builders->setRenderer($renderer);
    }

    /**
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->builders->setRenderer($renderer);
    }

    /**
     * @param string $name
     * @param  bool  $cache
     * @param string $default
     * @return string
     */
    public function getHTML(string $name, bool $cache = true, $default = ''): string
    {
        $domain = $this->source->findByName($name);
        if (empty($domain)) {
            return $default;
        }

        return $this->builders->getHTML($domain, $cache);
    }

    /**
     * @param string $name
     * @param  bool  $cache
     * @param array  $default
     * @return array
     */
    public function getTree(string $name, bool $cache = true, $default = []): array
    {
        $domain = $this->source->findByName($name);
        if (empty($domain)) {
            return $default;
        }

        return $this->builders->getTree($domain, $cache);
    }
}