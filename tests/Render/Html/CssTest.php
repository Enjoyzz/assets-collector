<?php

namespace Tests\Enjoys\AssetsCollector\Render\Html;

use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Render\Html\Css;
use PHPUnit\Framework\TestCase;

class CssTest extends TestCase
{
    public function testRenderHtmlCss(): void
    {
        $css = new Css(new Environment());
        $result = $css->getResult(
            [
                '1' => [],
                '2' => [],
                '//3' => []
            ]
        );

        $expect = <<<HTML
<link type='text/css' rel='stylesheet' href='1'>
<link type='text/css' rel='stylesheet' href='2'>
<link type='text/css' rel='stylesheet' href='//3'>

HTML;

        $this->assertSame(str_replace("\r", "", $expect), $result);
    }
}
