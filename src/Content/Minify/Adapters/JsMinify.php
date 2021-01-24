<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify\Adapters;

use Enjoys\AssetsCollector\Content\Minify\MinifyInterface;
use JShrink\Minifier;

/**
 * Class JsMinify
 * @package Enjoys\AssetsCollector\Content\Minify\Adapters
 */
class JsMinify implements MinifyInterface
{
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getContent(): string
    {
        return (string)Minifier::minify($this->content, array('flaggedComments' => false));
    }
}
