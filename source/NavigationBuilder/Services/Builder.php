<?php

namespace Spiral\NavigationBuilder\Services;

use Spiral\Database\Injections\Parameter;
use Spiral\NavigationBuilder\Database\Domain;
use Spiral\NavigationBuilder\Database\Link;
use Spiral\NavigationBuilder\Database\Sources\LinkSource;
use Spiral\NavigationBuilder\Database\Tree;
use Spiral\NavigationBuilder\DomainNavigation;
use Spiral\NavigationBuilder\Services\Builder\Stack;
use Spiral\NavigationBuilder\Services\Builder\KeysExtractor;
use Spiral\ORM\ORM;

class Builder
{
    /** @var LinkSource */
    private $linkSource;

    /** @var ORM */
    private $orm;

    /** @var DomainNavigation */
    private $domains;

    /** @var Stack */
    private $stack;

    /**
     * Builder constructor.
     *
     * @param LinkSource       $linkSource
     * @param ORM              $orm
     * @param DomainNavigation $domains
     */
    public function __construct(LinkSource $linkSource, ORM $orm, DomainNavigation $domains)
    {
        $this->linkSource = $linkSource;
        $this->orm = $orm;
        $this->domains = $domains;

        $this->stack = new Stack();
    }

    /**
     * pick all links in one collection ($tree->getKeys())
     * walk trough
     *
     * @param Domain $domain
     * @param array  $data
     */
    public function saveStructure(Domain $domain, array $data)
    {
        //data is same, no changes
        if ($data == $this->domains->getTree($domain)) {
            return;
        }

        //data changed, truncate domain tree
        $this->orm->table(Tree::class)->delete()->where([
            Tree::DOMAIN_ID => $domain->primaryKey()
        ]);

        $links = new KeysExtractor($data);
        $this->stack->setLinks($this->linkSource->find([
            'id' => ['IN' => new Parameter($links->getKeys())]
        ]));

        $this->recursiveCreateTree($domain, $data, 1);
        $this->domains->rebuild($domain);

        $domain->count_links = count($this->stack->getTreeKeys());
        $domain->save();

//todo, done, but need to count domains also
//        $query = $this->linkSource->findWithTree()->where([
//            'id' => ['IN' => new Parameter($links->getKeys())]
//        ])->compiledQuery();
//        $links = $query->columns(['link.id', 'count(*) as count_usages'])->groupBy('link.id');
//        foreach ($links as $item) {
//            $link = $this->stack->getLink($item['id']);
//            if (!empty($link)) {
//                $link->count_usages = $item['count_usages'];
//                $link->save();
//            }
//        }
    }

    /**
     * @param Domain $domain
     * @param array  $data
     * @param int    $depth
     * @param null   $parentLinkID
     * @param null   $parentTreeID
     */
    private function recursiveCreateTree(
        Domain $domain,
        array $data,
        int $depth,
        $parentLinkID = null,
        $parentTreeID = null
    ) {
        $order = 1;
        foreach ($data as $item) {
            $link = $this->stack->getLink($item['link']['id']);
            if (empty($link)) {
                //no link in db found by given id
                continue;
            }

            $parentLink = $this->stack->getLink($parentLinkID);
            if (!empty($parentLinkID) && empty($parentLink)) {
                //no parent link by given parent link id
                continue;
            }

            $parentTree = $this->stack->getTree($parentTreeID);
            if (!empty($parentTreeID) && empty($parentTree)) {
                //no parent tree by given parent tree id,
                //(should be in stack - came from previous call)
                continue;
            }

            $tree = $this->makeTreeRecord(
                $domain,
                $depth,
                $order,
                isset($item['link']['status']) ? $item['link']['status'] : null,
                $link,
                $parentLink,
                $parentTree
            );
            $this->stack->addTree($tree);

            if (!empty($item['sub']) && is_array($item['sub'])) {
                $this->recursiveCreateTree(
                    $domain,
                    $item['sub'],
                    $depth + 1,
                    $link->primaryKey(),
                    $tree->primaryKey()
                );
            }

            $order++;
        }
    }

    /**
     * @param Domain    $domain
     * @param int       $depth
     * @param int       $order
     * @param string    $status
     * @param Link      $link
     * @param Link|null $parentLink
     * @param Tree|null $parent
     * @return Tree
     */
    private function makeTreeRecord(
        Domain $domain,
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