<?php

namespace Spiral\NavigationBuilder\Services\Builder;

class KeysExtractor
{
    /** @var array */
    private $data;

    /** @var array|null */
    private $keys = null;

    /**
     * Tree constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        if (empty($this->keys)) {
            $this->keys = $this->recursiveKeyExtractor($this->data);
        }

        return $this->keys;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function recursiveKeyExtractor(array $data): array
    {
        $keys = [];
        foreach ($data as $item) {
            $keys[] = $item['link']['id'];
            if (!empty($item['sub']) && is_array($item['sub'])) {
                $keys = array_merge($keys, $this->recursiveKeyExtractor($item['sub']));
            }
        }

        return array_unique($keys);
    }
}