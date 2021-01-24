<?php

namespace Tests\Enjoys\AssetsCollector\Content\Minify\Adapters;

use Enjoys\AssetsCollector\Content\Minify\Adapters\CssMinify;
use PHPUnit\Framework\TestCase;

class CssMinifyTest extends TestCase
{
    public function testMinifyCss(): void
    {
        $content = <<<CSS
body { 
    color: red;
}
CSS;

        $minify = new CssMinify($content, []);
        $this->assertSame('body{color:red}', $minify->getContent());
    }
}
