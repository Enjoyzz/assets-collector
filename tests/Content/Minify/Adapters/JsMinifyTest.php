<?php

namespace Tests\Enjoys\AssetsCollector\Content\Minify\Adapters;

use Enjoys\AssetsCollector\Content\Minify\Adapters\JsMinify;
use PHPUnit\Framework\TestCase;

class JsMinifyTest extends TestCase
{
    public function testJsMinify(): void
    {
        $content = <<<JS
var hello = "Hello world";
//output
alert(hello);
JS;
        $minify = new JsMinify($content);
        $this->assertSame('var hello="Hello world";alert(hello);', $minify->getContent());
    }
}
