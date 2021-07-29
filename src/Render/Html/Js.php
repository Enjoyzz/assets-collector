<?php

namespace Enjoys\AssetsCollector\Render\Html;

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
     * @param string[] $paths
     * @return string
     */
    public function getResult(array $paths): string
    {
        $result = '';
        foreach ($paths as $path) {
            $result .= "<script src='{$path}{$this->environment->getVersion()}'></script>\n";
        }
        return $result;
    }
}
