<?php

namespace Spiral\NavigationBuilder;

use Spiral\Core\InjectableConfig;

class Config extends InjectableConfig
{
    const CONFIG = 'nav-builder';

    /**
     * {@inheritdoc}
     */
    protected $config = [
        'navigation' => 'tree',
        'link'       => 'link',
    ];

    /**
     * What view file renders full navigation tree.
     *
     * @return string
     */
    public function navigationView(): string
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