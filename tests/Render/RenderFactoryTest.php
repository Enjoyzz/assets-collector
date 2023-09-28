<?php

namespace Tests\Enjoys\AssetsCollector\Render;

use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Exception\UnexpectedParameters;
use Enjoys\AssetsCollector\Render\Html\Css;
use Enjoys\AssetsCollector\Render\RenderFactory;
use Enjoys\AssetsCollector\RenderInterface;
use PHPUnit\Framework\TestCase;

class RenderFactoryTest extends TestCase
{

    public function testGetRender(): void
    {
        $factory = RenderFactory::getRender('css', new Environment());
        $this->assertInstanceOf(RenderInterface::class, $factory);
        $this->assertInstanceOf(Css::class, $factory);
    }

    public function testGetRenderInvalid(): void
    {
        $this->expectException(UnexpectedParameters::class);
        RenderFactory::getRender('css', new Environment(), 'invalid');
    }
}
