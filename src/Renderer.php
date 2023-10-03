<?php

namespace Enjoys\AssetsCollector;

interface Renderer
{
    /**
     * @param Asset[] $assets
     * @return string
     */
    public function render(array $assets): string;
}
