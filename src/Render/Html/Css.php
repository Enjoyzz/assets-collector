<?php

namespace Enjoys\AssetsCollector\Render\Html;

use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Render\RenderInterface;

class Css implements RenderInterface
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
     * @param string[] $paths
     * @return string
     */
    public function getResult(array $paths): string
    {
        $result = '';
        foreach ($paths as $path) {
            $result .= "<link type='text/css' rel='stylesheet' href='{$path}{$this->environment->getVersion()}' />\n";
        }
        return $result;
    }
}
