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

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
