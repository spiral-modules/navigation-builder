<?php

namespace Spiral\NavigationBuilder\Database\Sources;

use Spiral\NavigationBuilder\Database\Link;
use Spiral\ORM\Entities\RecordSource;

class LinkSource extends RecordSource
{
    const RECORD = Link::class;

    public function findWithTree()
    {
        return $this->find()
            ->with('tree', ['alias' => 'trees'])
            ->load('tree', ['using' => 'trees']);
    }
}