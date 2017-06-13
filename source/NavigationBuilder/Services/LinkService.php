<?php

namespace Spiral\NavigationBuilder\Services;

use Spiral\Core\Service;
use Spiral\NavigationBuilder\Database\Link;
use Spiral\NavigationBuilder\Database\Sources\LinkSource;

class LinkService extends Service
{
    /** @var LinkSource */
    private $source;

    /** @var LinkWrapper */
    private $wrapper;

    /**
     * LinkService constructor.
     *
     * @param LinkSource  $source
     * @param LinkWrapper $linkWrapper
     */
    public function __construct(LinkSource $source, LinkWrapper $linkWrapper)
    {
        $this->source = $source;
        $this->wrapper = $linkWrapper;
    }

    /**
     * @param Link $link
     * @return bool
     */
    public function deleteAllowed(Link $link): bool
    {
        if ($link->tree->count() || $link->childrenTree->count()) {
            return false;
        }

        return true;
    }

    /**
     * Links list for vault builder.
     *
     * @return array
     */
    public function getList(): array
    {
        $output = [];

        /** @var Link $link */
        foreach ($this->source->find() as $link) {
            $output[(string)$link->primaryKey()] = $this->wrapper->wrapLink($link);
        }

        return $output;
    }

    /**
     * @param Link $link
     * @return Link
     */
    public function createCopy(Link $link): Link
    {
        $copy = new Link();
        $copy->text = $link->text;
        $copy->href = $link->href;
        $copy->attributes = $link->attributes;
        $copy->save();

        return $copy;
    }
}