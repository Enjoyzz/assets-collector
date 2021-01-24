<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify\Adapters;

use Enjoys\AssetsCollector\Content\Minify\MinifyInterface;

/**
 * Class NullMinify
 * @package Enjoys\AssetsCollector\Content\Minify\Adapters
 */
class NullMinify implements MinifyInterface
{
    private string $content;
    /**
     * @var array<mixed>
     */
    private array $minifyOptions;

    /**
     * NullMinify constructor.
     * @param string $content
     * @param array<mixed> $minifyOptions
     */
    public function __construct(string $content, array $minifyOptions)
    {
        $this->content = $content;
        $this->minifyOptions = $minifyOptions;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
