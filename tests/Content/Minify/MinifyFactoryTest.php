<?php

namespace Tests\Enjoys\AssetsCollector\Content\Minify;

use Enjoys\AssetsCollector\Content\Minify\Adapters\CssMinify;
use Enjoys\AssetsCollector\Content\Minify\MinifyFactory;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Exception\UnexpectedParameters;
use Enjoys\AssetsCollector\MinifyInterface;
use PHPUnit\Framework\TestCase;

class MinifyFactoryTest extends TestCase
{
    /**
     * @var Environment
     */
    private ?Environment $environment;


    protected function setUp(): void
    {
        $this->environment = new Environment('_compile', __DIR__ . '/../..');
        $this->environment->setMinifyCSS(new CssMinify());
    }

    protected function tearDown(): void
    {
        $this->environment = null;
    }

    public function testMinifyFactorySuccess(): void
    {
        $factory = MinifyFactory::minify('', 'css', $this->environment);
        $this->assertInstanceOf(MinifyInterface::class, $factory);
        $this->assertInstanceOf(CssMinify::class, $factory);
    }

    public function testMinifyFactoryInvalid(): void
    {
        $this->expectException(UnexpectedParameters::class);
        $factory = MinifyFactory::minify('', 'invalid', $this->environment);
    }
}
