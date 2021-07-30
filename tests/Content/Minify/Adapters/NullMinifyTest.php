<?php

namespace Tests\Enjoys\AssetsCollector\Content\Minify\Adapters;

use Enjoys\AssetsCollector\Content\Minify\Adapters\NullMinify;
use PHPUnit\Framework\TestCase;

class NullMinifyTest extends TestCase
{
    public function testNullMinify(): void
    {
        $content = <<<CSS
body { 
    color: red;
}
CSS;

        $minify = new NullMinify();
        $minify->setContent($content);
        $this->assertSame($content, $minify->getContent());
    }
}
