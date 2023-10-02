<?php

namespace Enjoys\AssetsCollector;

interface Renderer
{
    public function render(array $paths): string;
}
