<?php

namespace Spiral\NavigationBuilder\Services\Builder;

use Spiral\NavigationBuilder\Database\Attribute;
use Spiral\NavigationBuilder\Database\Link;
use Spiral\NavigationBuilder\Database\Tree;
use Spiral\ORM\Entities\RecordSelector;

class Stack
{
    /** @var array */
    private $tree = [];

    /** @var array */
    private $links = [];

    /**
     * @param $id
     * @return bool
     */
    public function hasLink($id): bool
    {
        return isset($this->links[$id]);
    }

    /**
     * @param Link $link
     */
    public function addLink(Link $link)
    {
        $this->links[$link->primaryKey()] = $link;
    }

    /**
     * @param $id
     * @return null|Link
     */
    public function getLink($id)
    {
        if (!$this->hasLink($id)) {
            return null;
        }

        return $this->links[$id];
    }

    /**
     * @param RecordSelector $selector
     */
    public function setLinks(RecordSelector $selector)
    {
        foreach ($selector as $link) {
            $this->addLink($link);
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasTree($id): bool
    {
        return isset($this->tree[$id]);
    }

    /**
     * @param Tree $tree
     */
    public function addTree(Tree $tree)
    {
        $this->tree[$tree->primaryKey()] = $tree;
    }

    /**
     * @param $id
     * @return Tree|null
     */
    public function getTree($id)
    {
        if (!$this->hasTree($id)) {
            return null;
        }

        return $this->tree[$id];
    }
}