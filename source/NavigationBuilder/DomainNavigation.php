<?php

namespace Spiral\NavigationBuilder;

use Psr\SimpleCache\CacheInterface;
use Spiral\NavigationBuilder\Builders\HtmlBuilder;
use Spiral\NavigationBuilder\Builders\TreeBuilder;
use Spiral\NavigationBuilder\Database\Domain;

/**
 * Layer with caching and rebuilding tree/html.
 * Class DomainNavigation
 *
 * @package Spiral\NavigationBuilder
 */
class DomainNavigation
{
    const TREE_CACHE = 'navigation::tree';
    const HTML_CACHE = 'navigation::html';
    const LIFETIME   = 86400 * 365 * 10; // 10 years is enough

    /** @var CacheInterface */
    private $cache;

    /** @var TreeBuilder */
    private $treeBuilder;

    /** @var HtmlBuilder */
    private $htmlBuilder;

    /** @var RendererInterface */
    private $renderer;

    /**
     * DomainNavigation constructor.
     *
     * @param CacheInterface    $cache
     * @param TreeBuilder       $treeBuilder
     * @param HtmlBuilder       $htmlBuilder
     * @param RendererInterface $renderer
     */
    public function __construct(
        CacheInterface $cache,
        TreeBuilder $treeBuilder,
        HtmlBuilder $htmlBuilder,
        RendererInterface $renderer
    ) {
        $this->cache = $cache;
        $this->treeBuilder = $treeBuilder;
        $this->htmlBuilder = $htmlBuilder;

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
     * @param Domain $domain
     */
    public function rebuild(Domain $domain)
    {
        $this->dropDomainCache($domain->name);

        $this->buildAndCacheTree($domain);
        $this->buildAndCacheHTML($domain);
    }

    public function dropDomainCache(string $domain)
    {
        $this->cache->delete($this->treeCache($domain));
        $this->cache->delete($this->htmlCache($domain));
    }

    /**
     * @param Domain $domain
     * @param bool   $cache
     * @return string
     */
    public function getHTML(Domain $domain, bool $cache = true): string
    {
        if (empty($cache)) {
            return $this->htmlBuilder->build($domain);
        }

        $html = $this->cache->get($this->htmlCache($domain));
        if (empty($html)) {
            $html = $this->buildAndCacheHTML($domain);
        }

        return $html;
    }

    /**
     * @param Domain $domain
     * @param bool   $cache
     * @return array
     */
    public function getTree(Domain $domain, bool $cache = true): array
    {
        if (empty($cache)) {
            return $this->treeBuilder->build($domain);
        }

        $tree = $this->cache->get($this->treeCache($domain));
        if (empty($tree)) {
            $tree = $this->buildAndCacheTree($domain);
        }

        return $tree;
    }

    /**
     * @param Domain $domain
     * @return array
     */
    protected function buildAndCacheTree(Domain $domain): array
    {
        $tree = $this->treeBuilder->build($domain);
        $this->cache->set($this->treeCache($domain->name), $tree, self::LIFETIME);

        return $tree;
    }

    /**
     * @param Domain $domain
     * @return string
     */
    protected function buildAndCacheHTML(Domain $domain): string
    {
        $html = $this->htmlBuilder->build($domain);
        $this->cache->set($this->htmlCache($domain->name), $html, self::LIFETIME);

        return $html;
    }

    /**
     * @param string $domain
     * @return string
     */
    protected function htmlCache(string $domain): string
    {
        return self::HTML_CACHE . '::' . $domain . '::' . get_class($this->renderer);
    }

    /**
     * @param string $domain
     * @return string
     */
    protected function treeCache(string $domain): string
    {
        return self::TREE_CACHE . '::' . $domain . '::' . get_class($this->renderer);
    }
}