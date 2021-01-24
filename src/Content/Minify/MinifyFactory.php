<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify;

use Enjoys\AssetsCollector\Content\Minify\Adapters;

/**
 * Class MinifyFactory
 * @package Enjoys\AssetsCollector\Content\Minify
 */
class MinifyFactory
{
    private const MINIFIES = [
        'css' => Adapters\CssMinify::class,
        'js' => Adapters\JsMinify::class
    ];

    /**
     * @param string $content
     * @param string $type
     * @return MinifyInterface
     */
    public static function minify(string $content, string $type): MinifyInterface
    {
        $minifyClass = Adapters\NullMinify::class;

        if (isset(self::MINIFIES[$type])) {
            $minifyClass = self::MINIFIES[$type];
        }
        return new $minifyClass($content);
    }
}
