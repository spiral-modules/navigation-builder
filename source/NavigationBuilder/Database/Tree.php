<?php
namespace Spiral\NavigationBuilder\Database;

use Spiral\NavigationBuilder\Database\Types\TreeStatus;
use Spiral\NavigationBuilder\Database\Types\TreeType;
use Spiral\ORM\Entities\Relations\BelongsToRelation;
use Spiral\ORM\Record;

/**
 * Class Tree
 *
 * @package Spiral\NavigationBuilder\Database
 * @property int                      $depth
 * @property int                      $order
 * @property TreeStatus               $status
 * @property TreeType                 $type
 * @property BelongsToRelation|Domain $domain
 * @property BelongsToRelation|Link   $link
 * @property BelongsToRelation|Tree   $parent
 * @property BelongsToRelation|Link   $parentLink
 */
class Tree extends Record
{
    const DATABASE = 'navigation';

    const DOMAIN_ID      = 'domain_id';
    const LINK_ID        = 'link_id';
    const PARENT_ID      = 'parent_id';
    const PARENT_LINK_ID = 'parent_link_id';
    const SCHEMA         = [
        'id'         => 'primary',
        'type'       => TreeType::class,   //has parent - is a child, otherwise is a parent
        'status'     => TreeStatus::class, //allows to hide link
        'domain'     => [
            self::BELONGS_TO => Domain::class,
            self::INNER_KEY  => self::DOMAIN_ID,
            Domain::INVERSE  => [Domain::HAS_MANY, 'tree']
        ],
        'link'       => [
            self::BELONGS_TO => Link::class,
            self::INNER_KEY  => self::LINK_ID,
            Link::INVERSE    => [Link::HAS_MANY, 'tree']
        ],
        'parent'     => [
            self::BELONGS_TO => self::class,
            self::INNER_KEY  => self::PARENT_ID,
            self::INVERSE    => [self::HAS_MANY, 'children']
        ],
        'parentLink' => [
            self::BELONGS_TO => Link::class,
            self::INNER_KEY  => self::PARENT_LINK_ID,
            self::INVERSE    => [self::HAS_MANY, 'childrenTree']
        ],
        'order'      => 'float',
        'depth'      => 'int',
    ];

    /**
     * Set status.
     *
     * @param string $status
     * @return bool
     */
    public function setStatus(string $status): bool
    {
        if (in_array($status, TreeStatus::VALUES)) {
            $this->status->setValue($status);

            return true;
        }

        return false;
    }
}