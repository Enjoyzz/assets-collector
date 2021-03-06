<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify;

interface MinifyInterface
{
    /**
     * MinifyInterface constructor.
     * @param string $content
     * @param array{css: array<mixed>, js: array<mixed>} $minifyOptions
     */
    public function __construct(string $content, array $minifyOptions);
    public function getContent(): string;
}
