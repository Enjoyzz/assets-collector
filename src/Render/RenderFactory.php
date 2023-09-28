<?php

namespace Enjoys\AssetsCollector\Render;

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Exception\UnexpectedParameters;
use Enjoys\AssetsCollector\Render\Html\Css;
use Enjoys\AssetsCollector\Render\Html\Js;
use Enjoys\AssetsCollector\RenderInterface;

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
        if (!array_key_exists($render, self::RENDERS)) {
            throw new UnexpectedParameters(
                sprintf('Invalid render group. Allowed only: %s', implode(', ', array_keys(self::RENDERS)))
            );
        }

        if (null === $renderClass = (self::RENDERS[$render][$type] ?? null)) {
            throw new UnexpectedParameters(
                sprintf('Invalid render. Allowed only: %s', implode(', ', array_keys(self::RENDERS[$render])))
            );
        }

        return new $renderClass($environment);
    }
}
