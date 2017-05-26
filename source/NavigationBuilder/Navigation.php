<?php

namespace Spiral\NavigationBuilder;

use Spiral\NavigationBuilder\Builders\HtmlBuilder;
use Spiral\NavigationBuilder\Builders\TreeBuilder;

/**
 * Layer with caching and rebuilding tree/html.
 * Class DomainNavigation
 *
 * @package Spiral\NavigationBuilder
 */
class Navigation
{
    const TREE_CACHE = 'navigation::tree';
    const HTML_CACHE = 'navigation::html';
    const LIFETIME   = 86400 * 365 * 10; // 10 years is enough

    /** @var TreeBuilder */
    private $treeBuilder;

    /** @var HtmlBuilder */
    private $htmlBuilder;

    /** @var RendererInterface */
    private $renderer;

    /** @var Storage */
    private $storage;

    /**
     * DomainNavigation constructor.
     *
     * @param TreeBuilder       $treeBuilder
     * @param HtmlBuilder       $htmlBuilder
     * @param RendererInterface $renderer
     * @param Storage           $storage
     */
    public function __construct(
        TreeBuilder $treeBuilder,
        HtmlBuilder $htmlBuilder,
        RendererInterface $renderer,
        Storage $storage
    ) {
        $this->treeBuilder = $treeBuilder;
        $this->htmlBuilder = $htmlBuilder;
        $this->storage = $storage;

        $this->setRenderer($renderer);
    }

    /**
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
        $this->htmlBuilder->setRenderer($renderer);
    }

    /**
     * @param string $domain
     */
    public function rebuild(string $domain)
    {
        $this->storage->dropDomainCache($domain, $this->renderer);

        $this->buildAndCacheTree($domain);
        $this->buildAndCacheHTML($domain);
    }

    /**
     * @param string $domain
     * @param bool   $cache
     * @return string
     */
    public function getHTML(string $domain, bool $cache = true): string
    {
        if (empty($cache)) {
            return $this->htmlBuilder->build($domain);
        }

        $html = $this->storage->getHtmlCache($domain, $this->renderer);
        if (empty($html)) {
            $html = $this->buildAndCacheHTML($domain);
        }

        return $html;
    }

    /**
     * @param string $domain
     * @param bool   $cache
     * @return array
     */
    public function getTree(string $domain, bool $cache = true): array
    {
        if (empty($cache)) {
            return $this->treeBuilder->build($domain);
        }

        $tree = $this->storage->getTreeCache($domain);
        if (empty($tree)) {
            $tree = $this->buildAndCacheTree($domain);
        }

        return $tree;
    }

    /**
     * @param string $domain
     * @return array
     */
    protected function buildAndCacheTree(string $domain): array
    {
        $tree = $this->treeBuilder->build($domain);
        $this->storage->setTreeCache($domain, $tree);

        return $tree;
    }

    /**
     * @param string $domain
     * @return string
     */
    protected function buildAndCacheHTML(string $domain): string
    {
        $html = $this->htmlBuilder->build($domain);
        $this->storage->setHtmlCache($domain, $this->renderer, $html);

        return $html;
    }
}