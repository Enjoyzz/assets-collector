<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

final class RenderFactory
{


    public static function getDefaultRenderer(AssetType $type): Renderer
    {
        return match ($type) {
            AssetType::CSS => self::createFromClosure(self::defaultCssRenderer()),
            AssetType::JS => self::createFromClosure(self::defaultJsRenderer())
        };
    }

    private static function defaultCssRenderer(): \Closure
    {
        /**
         * @param Asset[] $assets
         * @return string
         */
        return function (array $assets): string {
            $result = '';
            foreach ($assets as $asset) {
                $attributes = $asset->getAttributeCollection()
                    ->set('type', 'text/css')
                    ->set('rel', 'stylesheet')
                    ->getArray();

                krsort($attributes);

                $result .= sprintf(
                    "<link%s>\n",
                    new AttributeCollection($attributes)
                );
            }
            return $result;
        };
    }

    private static function defaultJsRenderer(): \Closure
    {
        /**
         * @param Asset[] $assets
         * @return string
         */
        return function (array $assets): string {
            $result = '';
            foreach ($assets as $asset) {
                $result .= sprintf(
                    "<script%s></script>\n",
                    $asset->getAttributeCollection()
                );
            }
            return $result;
        };
    }

    public static function createFromClosure(\Closure $closure): Renderer
    {
        return new class($closure) implements Renderer {

            /**
             * @param Closure(array): string $renderer
             */
            public function __construct(private readonly \Closure $renderer)
            {
            }

            public function render(array $paths): string
            {
                return call_user_func($this->renderer, $paths);
            }
        };
    }
}
