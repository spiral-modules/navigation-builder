<?php

namespace Spiral\NavigationBuilder\Services;

use Spiral\Database\Injections\Parameter;
use Spiral\NavigationBuilder\Database\Sources\LinkSource;
use Spiral\NavigationBuilder\Database\Sources\TreeSource;
use Spiral\NavigationBuilder\Database\Tree;
use Spiral\NavigationBuilder\Navigation;
use Spiral\NavigationBuilder\Services\Builder\Stack;
use Spiral\NavigationBuilder\Services\Builder\KeysExtractor;
use Spiral\ORM\ORM;

class Builder
{
    /** @var LinkSource */
    private $linkSource;

    /** @var TreeSource */
    private $treeSource;

    /** @var ORM */
    private $orm;

    /** @var Navigation */
    private $domains;

    /** @var Stack */
    private $stack;

    /**
     * Builder constructor.
     *
     * @param LinkSource $linkSource
     * @param TreeSource $treeSource
     * @param ORM        $orm
     * @param Navigation $domains
     */
    public function __construct(
        LinkSource $linkSource,
        TreeSource $treeSource,
        ORM $orm,
        Navigation $domains
    ) {
        $this->linkSource = $linkSource;
        $this->treeSource = $treeSource;
        $this->orm = $orm;
        $this->domains = $domains;

        $this->stack = new Stack();
    }

    /**
     * Creates db tree records based on passed tree from builder UI.
     * Store generated tree html in cache.
     *
     * @param string $domain
     * @param array  $data
     */
    public function saveStructure(string $domain, array $data)
    {
        //data is same, no changes
        if ($data == $this->domains->getTree($domain, false)) {
            return;
        }

        //data changed, truncate domain tree
        $this->deleteDomainTree($domain);

        //load all links from db
        $links = new KeysExtractor($data);
        $this->stack->setLinks($this->linkSource->find([
            'id' => ['IN' => new Parameter($links->getKeys())]
        ]));

        //Creates db tree records based on passed tree from builder UI
        $this->createTree($domain, $data, 1);
        $this->domains->rebuild($domain);

        $this->calculateCounters($links->getKeys());
    }

    /**
     * Truncate domain tree records.
     *
     * @param string $domain
     */
    private function deleteDomainTree(string $domain)
    {
        $this->orm->table(Tree::class)->delete()->where(compact('domain'));
    }

    /**
     * @param string $domain
     * @param array  $data
     * @param int    $depth
     * @param null   $parentLinkID
     * @param null   $parentTreeID
     */
    private function createTree(
        string $domain,
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

            $tree = $this->treeSource->createFromBuilder(
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
                $this->createTree(
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
     * Calculate link counters.
     *
     * @param array $keys
     */
    private function calculateCounters(array $keys)
    {
        /** @var SelectQuery $query */
        $query = $this->linkSource->findWithTree()->where([
            'id' => ['IN' => new Parameter($keys)]
        ])->compiledQuery();

        $links = $query
            ->columns(['link.id', 'trees.domain', 'count(*) as count'])
            ->groupBy('link.id, trees.domain');

        $data = [];
        foreach ($links as $item) {
            if (!isset($data[$item['id']])) {
                $data[$item['id']] = [
                    'domains' => [$item['domain']],
                    'count'   => $item['count']
                ];
            } else {
                $data[$item['id']]['domains'][] = $item['domain'];
                $data[$item['id']]['count'] += $item['count'];
            }
        }

        foreach ($data as $id => $item) {
            $link = $this->stack->getLink($id);
            if (!empty($link)) {
                $link->count_domains = count($item['domains']);
                $link->count_usages = count($item['count']);
                $link->save();
            }
        }
    }
}