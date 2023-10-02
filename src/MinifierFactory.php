<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

final class MinifierFactory
{

    public static function get(\Closure|Minifier|null $minifier): ?Minifier
    {
        if ($minifier instanceof \Closure){
            return self::createFromClosure($minifier);
        }

        if ($minifier instanceof Minifier){
            return $minifier;
        }

        return null;
    }

    public static function createFromClosure(\Closure $closure): Minifier
    {
        return new class($closure) implements Minifier {

            /**
             * @param Closure(string): string $minifier
             */
            public function __construct(private readonly \Closure $minifier)
            {
            }

            public function minify(string $content): string
            {
                return call_user_func($this->minifier, $content);
            }
        };
    }

}
