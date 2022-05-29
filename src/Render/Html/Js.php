<?php

namespace Enjoys\AssetsCollector\Render\Html;

use Enjoys\AssetsCollector\Attributes;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Render\RenderInterface;

class Js implements RenderInterface
{
    /**
     * @var Environment
     */
    private Environment $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }
    /**
     * @param array $paths
     * @return string
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    public function getResult(array $paths): string
    {
        $result = '';
        /** @var array<string, string|null>|null $attributes */
        foreach ($paths as $path => $attributes) {
            $result .= sprintf("<script%s src='{$path}{$this->environment->getVersion()}'></script>\n", (new Attributes($attributes))->__toString());
        }
        return $result;
    }
}
