<?php

namespace Spiral\NavigationBuilder\Database\Sources;

use Spiral\NavigationBuilder\Database\Link;
use Spiral\NavigationBuilder\Requests\LinkRequest;
use Spiral\ORM\Entities\RecordSelector;
use Spiral\ORM\Entities\RecordSource;

class LinkSource extends RecordSource
{
    const RECORD = Link::class;

    /**
     * @return $this|RecordSelector
     */
    public function findWithTree(): RecordSelector
    {
        $alias = 'trees';

        return $this->find()
            ->with('tree', ['alias' => $alias])
            ->load('tree', ['using' => $alias]);
    }

    /**
     * @param LinkRequest $request
     * @return Link
     */
    public function createFromRequest(LinkRequest $request): Link
    {
        $link = new Link();
        $link->setFields($request);
        $link->setAttributes($request->getAttributes());
        $link->save();

        return $link;
    }
}