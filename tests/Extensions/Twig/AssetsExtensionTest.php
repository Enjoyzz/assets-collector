<?php

namespace Tests\Enjoys\AssetsCollector\Extensions\Twig;

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Extensions\Twig\AssetsExtension;
use PHPUnit\Framework\TestCase;
use Twig\Loader\FilesystemLoader;

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

    public function dataForTestExtensionWithTwigLoader()
    {
        return [
            [
                new FilesystemLoader('/', __DIR__ . '/../../fixtures/twig_root_path'),
                "<link type='text/css' rel='stylesheet' href='/fixtures/twig_root_path/test.css' />\n<link type='text/css' rel='stylesheet' href='/fixtures/test.css' />\n"
            ],
            [null, "<link type='text/css' rel='stylesheet' href='/fixtures/test.css' />\n"]
        ];
    }

    /**
     * @dataProvider dataForTestExtensionWithTwigLoader
     */
    public function testExtensionWithTwigLoader($loader, $expect)
    {
        $environment = new Environment('_compile', __DIR__ . '/../..');
        $assetsCollector = new Assets($environment);
        $extension = new AssetsExtension($assetsCollector, $loader);

        $extension->asset('css', [
            'test.css',
            'tests/fixtures/test.css'
        ]);
        $this->assertSame(
            $expect,
            $extension->getExternCss()
        );
    }
}
