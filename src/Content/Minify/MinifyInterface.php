<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify;


interface MinifyInterface
{
    public function __construct(string $content);
    public function getContent(): string;
}