<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

interface Minifier
{
    public function minify(string $content): string;
}
