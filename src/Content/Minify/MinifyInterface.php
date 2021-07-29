<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify;

interface MinifyInterface
{
    /**
     * MinifyInterface constructor.
     * @param string $content
     * @param array{css: array, js: array} $minifyOptions
     */
    public function __construct(string $content, array $minifyOptions);
    public function getContent(): string;
}
