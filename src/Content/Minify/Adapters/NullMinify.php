<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify\Adapters;

use Enjoys\AssetsCollector\MinifyInterface;

/**
 * Class NullMinify
 * @package Enjoys\AssetsCollector\Content\Minify\Adapters
 */
class NullMinify implements MinifyInterface
{
    private string $content = '';

    public function __construct()
    {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
