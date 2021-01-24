<?php

namespace Enjoys\AssetsCollector\Render;

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Render\Html\Css;
use Enjoys\AssetsCollector\Render\Html\Js;

class RenderFactory
{
    private const RENDERS = [
        Assets::RENDER_HTML => [
            'css' => Css::class,
            'js' => Js::class
        ]
    ];

    /**
     * @param string $type
     * @param Environment $environment
     * @param string $render
     * @return RenderInterface
     * @throws \Exception
     */
    public static function getRender(
        string $type,
        Environment $environment,
        string $render = Assets::RENDER_HTML
    ): RenderInterface {
        if (!isset(self::RENDERS[$render][$type])) {
            throw new \Exception('Invalid render');
        }

        $renderClass = self::RENDERS[$render][$type];

        return new $renderClass($environment);
    }
}
