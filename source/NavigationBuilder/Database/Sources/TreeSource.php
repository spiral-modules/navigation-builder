<?php

namespace Spiral\NavigationBuilder\Database\Sources;

use Spiral\NavigationBuilder\Database\Link;
use Spiral\NavigationBuilder\Database\Tree;
use Spiral\NavigationBuilder\Database\Types\TreeStatus;
use Spiral\ORM\Entities\RecordSelector;
use Spiral\ORM\Entities\RecordSource;

class TreeSource extends RecordSource
{
    const RECORD = Tree::class;

    /**
     * @param string $domain
     * @return RecordSelector
     */
    public function findByDomain(string $domain): RecordSelector
    {
        return $this->find(compact('domain'));
    }

    /**
     * @param string $domain
     * @param bool   $publicOnly
     * @return RecordSelector
     */
    public function findDomainTree(string $domain, bool $publicOnly = true): RecordSelector
    {
        $alias = 'links';
        $query = compact('domain');

        if (!empty($publicOnly)) {
            $query['status'] = TreeStatus::ACTIVE;
        }

        return $this->find($query)
            ->with('link', ['alias' => $alias])
            ->load('link', ['using' => $alias]);
    }

    /**
     * @param string    $domain
     * @param int       $depth
     * @param int       $order
     * @param string    $status
     * @param Link      $link
     * @param Link|null $parentLink
     * @param Tree|null $parent
     * @return Tree
     */
    public function createFromBuilder(
        string $domain,
        int $depth,
        int $order,
        string $status,
        Link $link,
        Link $parentLink = null,
        Tree $parent = null
    ): Tree
    {
        $tree = new Tree();
        $tree->domain = $domain;
        $tree->depth = $depth;
        $tree->order = $order;

        if (!empty($status)) {
            $tree->status->setValue($status);
        }

        $tree->link = $link;
        if (!empty($parentLink)) {
            $tree->parentLink = $parentLink;
        }
        if (!empty($parent)) {
            $tree->parent = $parent;
            $tree->type->setChild();
        } else {
            $tree->type->setParent();
        }

        $tree->save();

        return $tree;
    }
}