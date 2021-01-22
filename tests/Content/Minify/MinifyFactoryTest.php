<?php

namespace Tests\Enjoys\AssetsCollector\Content\Minify;

use Enjoys\AssetsCollector\Content\Minify\Adapters\CssMinify;
use Enjoys\AssetsCollector\Content\Minify\Adapters\NullMinify;
use Enjoys\AssetsCollector\Content\Minify\MinifyFactory;
use Enjoys\AssetsCollector\Content\Minify\MinifyInterface;
use PHPUnit\Framework\TestCase;

class MinifyFactoryTest extends TestCase
{
    public function testMinifyFactorySuccess(): void
    {
        $factory = MinifyFactory::minify('', 'css');
        $this->assertInstanceOf(MinifyInterface::class, $factory);
        $this->assertInstanceOf(CssMinify::class, $factory);
    }

    public function testMinifyFactoryInvalid(): void
    {
        $factory = MinifyFactory::minify('', 'invalid');
        $this->assertInstanceOf(MinifyInterface::class, $factory);
        $this->assertInstanceOf(NullMinify::class, $factory);
    }
}
