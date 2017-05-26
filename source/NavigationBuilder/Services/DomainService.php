<?php

namespace Spiral\NavigationBuilder\Services;

use Spiral\Core\Service;
use Spiral\NavigationBuilder\Config;

class DomainService extends Service
{
    /** @var Config  */
    private $config;

    /**
     * DomainService constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $domain
     * @return bool
     */
    public function domainExists(string $domain): bool
    {
        foreach ($this->config->domains() as $item) {
            if (strcasecmp($item, $domain)) {
                return true;
            }
        }

        return false;
    }
}