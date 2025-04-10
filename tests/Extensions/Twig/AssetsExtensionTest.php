<?php

namespace Tests\Enjoys\AssetsCollector\Extensions\Twig;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Extensions\Twig\AssetsExtension;
use PHPUnit\Framework\TestCase;
use Tests\Enjoys\AssetsCollector\HelpersTestTrait;
use Twig\Loader\FilesystemLoader;

class AssetsExtensionTest extends TestCase
{

    use HelpersTestTrait;

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

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__ . '/../../_compile', true);
    }

    public function testGetFunctions()
    {
        $this->assertCount(3, $this->extension->getFunctions());
    }

    public function testAsset()
    {
        $this->assertSame(
            "<link type='text/css' rel='stylesheet' href='http://google.com'>\n<link type='text/css' rel='stylesheet' href='http://yandex.ru'>\n",
            $this->assetsCollector->get(AssetType::CSS)
        );
        $this->assertSame(
            "<script src='http://google.com'></script>\n",
            $this->assetsCollector->get(AssetType::JS)
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
            "<link type='text/css' rel='stylesheet' href='http://google.com'>\n<link type='text/css' rel='stylesheet' href='http://yandex.ru'>\n",
            $this->extension->getExternCss()
        );
    }

    public function dataForTestExtensionWithTwigLoader()
    {
        return [
            [
                new FilesystemLoader('/', __DIR__ . '/../../fixtures/twig_root_path'),
                "<link rel='stylesheet' href='http://yandex.ru'>\n<link type='text/css' rel='stylesheet' href='/fixtures/twig_root_path/test.css'>\n<link type='text/css' rel='stylesheet' href='/fixtures/test.css'>\n"
            ],
            [null, "<link rel='stylesheet' href='http://yandex.ru'>\n<link type='text/css' rel='stylesheet' href='/fixtures/test.css'>\n"]
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
            [
                '//yandex.ru',
                AssetOption::ATTRIBUTES => [
                    'type' => false
                ],
                AssetOption::NOT_COLLECT => true,
                AssetOption::MINIFY => false
            ],
            'test.css',
            'tests/fixtures/test.css'
        ]);
        $this->assertSame(
            $expect,
            $extension->getExternCss()
        );
    }
}
