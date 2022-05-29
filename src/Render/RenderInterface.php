<?php

namespace Enjoys\AssetsCollector\Render;

interface RenderInterface
{
    public function getResult(array $paths): string;
}
