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
        return function (array $paths): string {
            $result = '';
            /** @var array<string, string|null>|null $attributes */
            foreach ($paths as $path => $attributes) {
                $attributes = array_merge(['type' => 'text/css', 'rel' => 'stylesheet'], (array)$attributes);
                $result .= sprintf(
                    "<link%s href='%s'>\n",
                    (new Attributes($attributes))->__toString(),
                    $path
                );
            }
            return $result;
        };
    }

    private static function defaultJsRenderer(): \Closure
    {
        return function (array $paths): string {
            $result = '';
            /** @var array<string, string|null>|null $attributes */
            foreach ($paths as $path => $attributes) {
                $result .= sprintf(
                    "<script%s src='%s'></script>\n",
                    (new Attributes($attributes))->__toString(),
                    $path
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
