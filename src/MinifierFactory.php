<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use Closure;

final class MinifierFactory
{

    /**
     * @param Closure(string):string|Minifier|null $minifier
     * @return Minifier|null
     */
    public static function get(Closure|Minifier|null $minifier): ?Minifier
    {
        if ($minifier instanceof Minifier || $minifier === null){
            return $minifier;
        }

        return self::createFromClosure($minifier);
    }

    /**
     * @param Closure(string): string $closure
     * @return Minifier
     */
    private static function createFromClosure(Closure $closure): Minifier
    {
        return new class($closure) implements Minifier {

            /**
             * @param Closure(string): string $minifier
             */
            public function __construct(private readonly Closure $minifier)
            {
            }

            public function minify(string $content): string
            {
                return call_user_func($this->minifier, $content);
            }
        };
    }

}
