<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify\Adapters;

use Enjoys\AssetsCollector\Content\Minify\MinifyInterface;
use Enjoys\Traits\Options;
use JShrink\Minifier;

/**
 * Class JsMinify
 * @package Enjoys\AssetsCollector\Content\Minify\Adapters
 */
class JsMinify implements MinifyInterface
{
    use Options;

    private string $content;

    /**
     * JsMinify constructor.
     * @param string $content
     * @param array<mixed> $minifyOptions
     */
    public function __construct(string $content, array $minifyOptions)
    {
        $this->content = $content;
        $this->setOptions($minifyOptions);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getContent(): string
    {
        return (string)Minifier::minify(
            $this->content,
            [
                'flaggedComments' => $this->getOption('flaggedComments', false)
            ]
        );
    }
}
