<?php

namespace Spiral\NavigationBuilder\Database;

use Spiral\ORM\Entities\Relations\HasManyRelation;
use Spiral\ORM\Record;

/**
 * Class Domain
 *
 * @package Spiral\NavigationBuilder\Database
 * @property HasManyRelation|Tree[] $tree
 * @property string                 $name
 * @property int                    $count_links
 */
class Domain extends Record
{
    const DATABASE = 'navigation';

    const SCHEMA = [
        'id'          => 'primary',
        'name'        => 'string',
        'count_links' => 'int',
    ];
}