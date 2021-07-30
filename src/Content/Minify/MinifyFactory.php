<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify;

use Enjoys\AssetsCollector\Content\Minify\Adapters;
use Enjoys\AssetsCollector\Environment;

/**
 * Class MinifyFactory
 * @package Enjoys\AssetsCollector\Content\Minify
 */
class MinifyFactory
{

    /**
     * @param string $content
     * @param string $type
     * @param Environment $environment
     * @return MinifyInterface
     */
    public static function minify(string $content, string $type, Environment $environment): MinifyInterface
    {
        $minify = $environment->getMinify($type);
        $minify->setContent($content);
        return $minify;
    }
}
