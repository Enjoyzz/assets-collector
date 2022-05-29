<?php

namespace Tests\Enjoys\AssetsCollector\Render\Html;

use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Render\Html\Js;
use PHPUnit\Framework\TestCase;

class JsTest extends TestCase
{
    public function testRenderHtmlJs(): void
    {
        $js = new Js(new Environment());
        $result = $js->getResult(
            [
                '1' => null,
                '2' => null,
                '//3' => null
            ]
        );

        $expect = <<<HTML
<script src='1'></script>
<script src='2'></script>
<script src='//3'></script>

HTML;

        $this->assertSame(str_replace("\r", "", $expect), $result);
    }
}
