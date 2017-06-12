<?php

namespace Spiral\NavigationBuilder;

use Spiral\Core\InjectableConfig;

class NavigationBuilderConfig extends InjectableConfig
{
    const CONFIG = 'modules/nav-builder';

    /**
     * {@inheritdoc}
     */
    protected $config = [
        'views'   => [
            'tree' => 'navigation:tree',
            'link' => 'navigation:link',
        ],
        'domains' => ['default']
    ];

    /**
     * List of domains.
     *
     * @return array
     */
    public function domains(): array
    {
        return $this->config['domains'];
    }

    /**
     * What view file renders full navigation tree.
     *
     * @return string
     */
    public function treeView(): string
    {
        return $this->config['views']['tree'];
    }

    /**
     * What view file renders single navigation item (anchor).
     *
     * @return string
     */
    public function linkView(): string
    {
        return $this->config['views']['link'];
    }
}