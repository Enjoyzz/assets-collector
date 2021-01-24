<?php

namespace Enjoys\AssetsCollector\Render;

interface RenderInterface
{
    /**
     * @param array<string> $paths
     * @return string
     */
    public function getResult(array $paths): string;
}
