<?php

namespace Spiral\NavigationBuilder\Database\Types;

use Spiral\ORM\Columns\EnumColumn;

class TreeType extends EnumColumn
{
    const PARENT  = 'parent';
    const CHILD   = 'child';
    const VALUES  = [self::PARENT, self::CHILD];
    const DEFAULT = self::PARENT;

    public function setParent()
    {
        $this->setValue(self::PARENT);
    }

    public function setChild()
    {
        $this->setValue(self::CHILD);
    }
}