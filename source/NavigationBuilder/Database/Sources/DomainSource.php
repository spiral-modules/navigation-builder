<?php

namespace Spiral\NavigationBuilder\Database\Sources;

use Spiral\NavigationBuilder\Database\Domain;
use Spiral\ORM\Entities\RecordSource;

class DomainSource extends RecordSource
{
    const RECORD = Domain::class;

    /**
     * @param string $name
     * @return null|Domain
     */
    public function findByName(string $name)
    {
        $name = strtolower(trim($name));

        return $this->findOne(compact('name'));
    }
}