<?php

namespace Spiral\NavigationBuilder\Services;

use Spiral\Core\Service;
use Spiral\NavigationBuilder\NavigationBuilderConfig;

class DomainService extends Service
{
    /** @var NavigationBuilderConfig  */
    private $config;

    /**
     * DomainService constructor.
     *
     * @param NavigationBuilderConfig $config
     */
    public function __construct(NavigationBuilderConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $domain
     * @return bool
     */
    public function domainExists(string $domain): bool
    {
        foreach ($this->config->domains() as $name => $label) {
            if (strcasecmp($name, $domain) === 0) {
                return true;
            }
        }

        return false;
    }
}