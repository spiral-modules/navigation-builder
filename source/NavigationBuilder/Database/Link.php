<?php
namespace Spiral\NavigationBuilder\Database;

use Spiral\NavigationBuilder\Services\LinkWrapper;
use Spiral\ORM\Record;

/**
 * Class Link
 *
 * @package Spiral\NavigationBuilder\Database
 * @property string $text
 * @property string $href
 * @property string $name
 * @property string $attributes
 */
class Link extends Record
{
    const DATABASE = 'navigation';

    const SCHEMA = [
        'id'            => 'primary',
        'text'          => 'string',
        'href'          => 'text',
        'attributes'    => 'text',
        'count_domains' => 'int',
        'count_usages'  => 'int',
    ];

    const FILLABLE = [
        'text',
        'href'
    ];

    /**
     * @param array $value
     */
    public function setAttributes(array $value)
    {
        $this->attributes = LinkWrapper::packAttributes($value);
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return LinkWrapper::unpackAttributes($this->attributes);
    }

    /**
     * @param string $value
     * @return array
     */
    public static function attributes(string $value): array
    {
        return (array)json_decode($value, true);
    }
}