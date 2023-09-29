<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

interface Minify
{
    public function minify(string $content): string;
}
