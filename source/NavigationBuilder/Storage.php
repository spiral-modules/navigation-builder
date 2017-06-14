<?php

namespace Spiral\NavigationBuilder;

use Psr\SimpleCache\CacheInterface;

/**
 * Class Storage. A bridge for cache or other storage.
 *
 * @package Spiral\NavigationBuilder
 */
class Storage
{
    const TREE_CACHE      = 'navigation::tree';
    const STRUCTURE_CACHE = 'navigation::structure';
    const HTML_CACHE      = 'navigation::html';
    const LIFETIME        = 86400 * 365 * 10; // 10 years is enough

    /** @var CacheInterface */
    private $cache;

    /**
     * Storage constructor.
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Drop all domain cache with defined renderer.
     *
     * @param string            $domain
     * @param RendererInterface $renderer
     */
    public function dropDomainCache(string $domain, RendererInterface $renderer)
    {
        $this->cache->delete($this->treeCache($domain));
        $this->cache->delete($this->htmlCache($domain, $renderer));
    }

    /**
     * Get domain tree cache. Supposed to be array.
     *
     * @param string $domain
     * @return mixed
     */
    public function getTreeCache(string $domain)
    {
        return $this->cache->get($this->treeCache($domain), []);
    }

    /**
     * Set domain tree cache.
     *
     * @param string $domain
     * @param array  $data
     */
    public function setTreeCache(string $domain, array $data)
    {
        $this->cache->set($this->treeCache($domain), $data, self::LIFETIME);
    }

    /**
     * Get domain structure cache. Supposed to be array.
     *
     * @param string $domain
     * @return mixed
     */
    public function getStructureCache(string $domain)
    {
        return $this->cache->get($this->structureCache($domain), []);
    }

    /**
     * Set domain structure cache.
     *
     * @param string $domain
     * @param array  $data
     */
    public function setStructureCache(string $domain, array $data)
    {
        $this->cache->set($this->structureCache($domain), $data, self::LIFETIME);
    }

    /**
     * Get domain html cache. Supposed to be string.
     *
     * @param string            $domain
     * @param RendererInterface $renderer
     * @return mixed
     */
    public function getHtmlCache(string $domain, RendererInterface $renderer)
    {
        return $this->cache->get($this->htmlCache($domain, $renderer));
    }

    /**
     * Set domain html cache.
     *
     * @param string            $domain
     * @param RendererInterface $renderer
     * @param string            $data
     */
    public function setHtmlCache(string $domain, RendererInterface $renderer, string $data)
    {
        $this->cache->set($this->htmlCache($domain, $renderer), $data, self::LIFETIME);
    }

    /**
     * Tree cache name.
     *
     * @param string $domain
     * @return string
     */
    protected function treeCache(string $domain): string
    {
        return self::TREE_CACHE . '::' . $domain;
    }

    /**
     * Tree cache name.
     *
     * @param string $domain
     * @return string
     */
    protected function structureCache(string $domain): string
    {
        return self::STRUCTURE_CACHE . '::' . $domain;
    }

    /**
     * HTML cache name (used renderer).
     *
     * @param string            $domain
     * @param RendererInterface $renderer
     * @return string
     */
    protected function htmlCache(string $domain, RendererInterface $renderer): string
    {
        return self::HTML_CACHE . '::' . $domain . '::' . get_class($renderer);
    }
}