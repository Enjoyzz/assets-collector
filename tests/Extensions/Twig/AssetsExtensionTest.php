<?php

namespace Tests\Enjoys\AssetsCollector\Extensions\Twig;

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Extensions\Twig\AssetsExtension;
use PHPUnit\Framework\TestCase;

class AssetsExtensionTest extends TestCase
{
    /**
     * @var Assets
     */
    private Assets $assetsCollector;
    /**
     * @var AssetsExtension
     */
    private AssetsExtension $extension;

    protected function setUp(): void
    {
        $environment = new Environment('_compile', __DIR__ . '/../..');
        $environment->setBaseUrl('/foo');
        $this->assetsCollector = new Assets($environment);
        $this->extension = new AssetsExtension($this->assetsCollector);
        $this->extension->asset('css', ['//google.com', '//yandex.ru']);
        $this->extension->asset('js', ['//google.com']);
    }

    public function testGetFunctions()
    {
        $this->assertCount(3, $this->extension->getFunctions());
    }

    public function testAsset()
    {
        $this->assertSame(
            "<link type='text/css' rel='stylesheet' href='http://google.com' />\n<link type='text/css' rel='stylesheet' href='http://yandex.ru' />\n",
            $this->assetsCollector->get('css')
        );
        $this->assertSame(
            "<script src='http://google.com'></script>\n",
            $this->assetsCollector->get('js')
        );
    }



    public function testGetExternJs()
    {
        $this->assertSame(
            "<script src='http://google.com'></script>\n",
            $this->extension->getExternJs()
        );
    }

    public function testGetExternCss()
    {
        $this->assertSame(
            "<link type='text/css' rel='stylesheet' href='http://google.com' />\n<link type='text/css' rel='stylesheet' href='http://yandex.ru' />\n",
            $this->extension->getExternCss()
        );
    }
}
