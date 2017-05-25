<?php

namespace Spiral\NavigationBuilder\Services;

use Spiral\NavigationBuilder\Database\Link;

class LinkWrapper
{
    /**
     * @param Link $link
     * @return array
     */
    public function wrapLink(Link $link): array
    {
        $output = $link->getFields();
        $output['attributes'] = $link->getAttributes();

        return $output;
    }

    /**
     * @param string $value
     * @return array
     */
    public static function unpackAttributes(string $value): array
    {
        return (array)json_decode($value, true);
    }

    /**
     * @param array $value
     * @return string
     */
    public static function packAttributes(array $value): string
    {
        return json_encode($value);
    }
}