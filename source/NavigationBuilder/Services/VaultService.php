<?php

namespace Spiral\NavigationBuilder\Services;

use Spiral\Core\Service;
use Spiral\NavigationBuilder\Database\Domain;
use Spiral\NavigationBuilder\Database\Link;
use Spiral\NavigationBuilder\Database\Sources\DomainSource;
use Spiral\NavigationBuilder\Database\Sources\LinkSource;

class VaultService extends Service
{
    private $domains;
    private $links;
    private $linkWrapper;

    public function __construct(
        DomainSource $domainSource,
        LinkSource $linkSource,
        LinkWrapper $linkWrapper
    ) {
        $this->domains = $domainSource;
        $this->links = $linkSource;
        $this->linkWrapper = $linkWrapper;
    }

    /**
     * Domains list for vault builder.
     *
     * @return array
     */
    public function getDomainsList(): array
    {
        $output = [];

        /** @var Domain $domain */
        foreach ($this->domains->find() as $domain) {
            $output[$domain->primaryKey()] = $domain->name;
        }

        return $output;
    }

    /**
     * Links list for vault builder.
     *
     * @return array
     */
    public function getLinksList(): array
    {
        $output = [];

        /** @var Link $link */
        foreach ($this->links->find() as $link) {
            $output[$link->primaryKey()] = $this->linkWrapper->wrapLink($link);
        }

        return $output;
    }
}