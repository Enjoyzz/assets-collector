<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify;

interface MinifyInterface
{
    public function getContent(): string;

    public function setContent(string $content): void;
}
