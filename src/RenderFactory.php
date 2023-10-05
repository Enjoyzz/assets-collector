<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use Closure;

final class RenderFactory
{


    public static function getDefaultRenderer(AssetType $type): Renderer
    {
        return match ($type) {
            AssetType::CSS => self::createFromClosure(self::defaultCssRenderer()),
            AssetType::JS => self::createFromClosure(self::defaultJsRenderer())
        };
    }

    /**
     * @return Closure(Asset[]): string
     */
    private static function defaultCssRenderer(): Closure
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
                    (new AttributeCollection($attributes))->__toString()
                );
            }
            return $result;
        };
    }

    /**
     * @return Closure(Asset[]): string
     */
    private static function defaultJsRenderer(): Closure
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
                    $asset->getAttributeCollection()->__toString()
                );
            }
            return $result;
        };
    }

    /**
     * @param Closure(Asset[]): string $closure
     * @return Renderer
     */
    private static function createFromClosure(Closure $closure): Renderer
    {
        return new class($closure) implements Renderer {

            /**
             * @param Closure(Asset[]): string $renderer
             */
            public function __construct(private readonly Closure $renderer)
            {
            }

            public function render(array $assets): string
            {
                return call_user_func($this->renderer, $assets);
            }
        };
    }
}
