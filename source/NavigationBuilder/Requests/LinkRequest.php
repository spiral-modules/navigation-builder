<?php

namespace Spiral\NavigationBuilder\Requests;

use Spiral\Http\Request\RequestFilter;

class LinkRequest extends RequestFilter
{
    const SCHEMA = [
        'href'       => 'data:href',
        'text'       => 'data:text',
        'attributes' => 'data:attributes',
    ];

    const VALIDATES = [
        ['href' => ['not_empty']],
        ['text' => ['not_empty']],
        ['attributes' => ['is_array']],
    ];

    const SETTERS = [
        'text'       => 'trim',
        'href'       => 'trim',
        'attributes' => [self::class, 'trimAttributes'],
    ];

    /**
     * @param array $attributes
     * @return array
     */
    public static function trimAttributes(array $attributes): array
    {
        $output = [];
        foreach ($attributes as $name => $value) {
            $name = trim($name);
            if (!empty($name)) {
                $output[$name] = trim($value);
            }
        }

        return $output;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
