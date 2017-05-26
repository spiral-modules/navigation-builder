<?php

namespace Spiral\NavigationBuilder;

use Spiral\Core\InjectableConfig;

class Config extends InjectableConfig
{
    const CONFIG = 'modules/nav-builder';

    /**
     * {@inheritdoc}
     */
    protected $config = [
        'tree'    => 'navigation:tree',
        'link'    => 'navigation:link',
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
        return $this->config['navigation'];
    }

    /**
     * What view file renders single navigation item (anchor).
     *
     * @return string
     */
    public function linkView(): string
    {
        return $this->config['link'];
    }
}