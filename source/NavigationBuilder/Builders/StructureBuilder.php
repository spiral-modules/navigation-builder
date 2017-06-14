<?php

namespace Spiral\NavigationBuilder\Builders;

use Spiral\NavigationBuilder\Services\LinkWrapper;

class StructureBuilder extends TreeBuilder
{
    /**
     * @param array $data
     * @return array
     */
    protected function packRow(array $data): array
    {
        return parent::packRow($data) + [
            'depth'      => $data['depth'],
            'text'       => $data['link']['text'],
            'href'       => $data['link']['href'],
            'attributes' => LinkWrapper::unpackAttributes($data['link']['attributes']),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    protected function packLink(array $data): array
    {
        return parent::packLink($data) + [
            'depth'      => $data['depth'],
            'text'       => $data['text'],
            'href'       => $data['href'],
            'attributes' => $data['attributes'],
        ];
    }

    /**
     * @param string $domain
     * @return array
     */
    protected function getTree(string $domain): array
    {
        return $this->source->findDomainTree($domain)->orderBy('order', 'ASC')->fetchData();
    }
}