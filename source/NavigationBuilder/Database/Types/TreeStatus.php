<?php

namespace Spiral\NavigationBuilder\Database\Types;

use Spiral\ORM\Columns\EnumColumn;

class TreeStatus extends EnumColumn
{
    const ACTIVE   = 'active';
    const DISABLED = 'disabled';
    const VALUES   = [self::ACTIVE, self::DISABLED];
    const DEFAULT  = self::ACTIVE;
}