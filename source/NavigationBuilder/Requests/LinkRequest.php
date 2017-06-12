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
        'href'       => [self::class, 'trimHref'],
        'attributes' => [self::class, 'trimAttributes'],
    ];

    /**
     * @param string $value
     * @return string
     */
    public static function trimHref(string $value): string
    {
        $value = trim($value, ' /');
        if (stripos($value, 'http://') === 0 || stripos($value, 'https://') === 0) {
            //absolute path
            return $value;
        }

        //relative path
        return '/' . $value;
    }

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
