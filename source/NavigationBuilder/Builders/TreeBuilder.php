<?php

namespace Spiral\NavigationBuilder\Builders;

use Spiral\NavigationBuilder\Database\Sources\TreeSource;
use Spiral\NavigationBuilder\Database\Tree;

class TreeBuilder
{
    /** @var TreeSource */
    protected $source;

    /**
     * TreeBuilder constructor.
     *
     * @param TreeSource $source
     */
    public function __construct(TreeSource $source)
    {
        $this->source = $source;
    }

    /**
     * @param string $domain
     * @return array
     */
    public function build(string $domain)
    {
        $map = [];
        foreach ($this->getTree($domain) as $item) {
            $row = $this->packRow($item);

            $map[$row['parentID']][$row['id']] = $row;
        }

        return $this->recursive($map, null);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function packRow(array $data): array
    {
        return [
            'id'       => $data['id'],
            'status'   => $data['status'],
            'parentID' => $data[Tree::PARENT_ID] ? $data[Tree::PARENT_ID] : null,
            'linkID'   => $data[Tree::LINK_ID]
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    protected function packLink(array $data): array
    {
        return [
            'id'     => $data['linkID'],
            'tree'   => $data['id'],
            'status' => $data['status']
        ];
    }

    /**
     * @param string $domain
     * @return array
     */
    protected function getTree(string $domain): array
    {
        return $this->source->findByDomain($domain)->orderBy('order', 'ASC')->fetchData();
    }

    /**
     * @param array $input
     * @param int   $parentID
     * @return array
     */
    private function recursive(array $input, $parentID): array
    {
        if (!isset($input[$parentID])) {
            return [];
        }

        $output = [];
        foreach ($input[$parentID] as $data) {
            $output[] = [
                'link' => $this->packLink($data),
                'sub'  => $this->recursive($input, $data['id'])
            ];
        }

        return $output;
    }
}