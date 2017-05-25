<?php

namespace Spiral\NavigationBuilder;

interface RendererInterface
{
    /**
     * @param array $link
     * @return string
     */
    public function link(array $link): string;

    /**
     * @param array $navigation
     * @return string
     */
    public function navigation(array $navigation): string;
}