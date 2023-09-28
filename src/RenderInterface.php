<?php

namespace Enjoys\AssetsCollector;

interface RenderInterface
{
    public function getResult(array $paths): string;
}
