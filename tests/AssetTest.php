<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;

class AssetTest extends TestCase
{

    use HelpersTestTrait;

    /**
     * @var Environment
     */
    private Environment $environment;

    protected function setUp(): void
    {
        $this->environment = new Environment(__DIR__ . '/_compile', __DIR__ . '/../');
        $this->environment->setBaseUrl('/t');
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__ . '/_compile', true);
        $this->removeDirectoryRecursive(__DIR__ . '/tests', true);

    }

    /**
     * 'type', 'path', [], 'isUrl', 'getPath', 'isMinify', 'getType', 'setId?'
     */
    public function data(): array
    {
        $this->setUp();

        return [
            [AssetType::CSS, '//test', [], true, 'http://test', true, 'css', true],
            [AssetType::CSS, 'http://test', [], true, 'http://test', true, 'css', true],
            [AssetType::CSS, 'https://test', [], true, 'https://test', true, 'css', true],
            [
                AssetType::CSS,
                __DIR__ . '/fixtures/test.css',
                [],
                false,
                __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'test.css',
                true,
                'css',
                true
            ],
            [AssetType::CSS, __DIR__ . '/../README.md', [], false, realpath(__DIR__ . '/../README.md'), true, 'css', true],
            [AssetType::CSS, '../README.md', [], false, '', true, 'css', false],
            [AssetType::CSS, '../README.md', [AssetOption::MINIFY => false], false, '', false, 'css', false],
            [AssetType::CSS, 'tests/fixtures/test.css', [AssetOption::MINIFY => false], false, $this->environment->getProjectDir().'/tests/fixtures/test.css', false, 'css', true],
        ];
    }

    /**
     * @dataProvider data
     */
    public function test__construct($type, $path, $params, $isUrl, $getPath, $isMinify, $getType, $setId)
    {
        chdir(__DIR__.'/fixtures');
        $asset = new Asset($type, $path, $params);
        $this->assertSame($isUrl, $asset->isUrl());
        $this->assertSame($getPath, $asset->getPath());
        $this->assertSame($isMinify, $asset->getOptions()->isMinify());
        $this->assertSame($getType, $asset->getType()->value);
        $this->assertSame($setId, !empty($asset->getId()));
    }

    public function testAssetIfEnv_ASSETS_PROJECT_DIRECTORY_IsFalse()
    {
        chdir(__DIR__.'/fixtures');
        putenv('ASSETS_PROJECT_DIRECTORY');
        $asset = new Asset(AssetType::CSS, 'tests/fixtures/test.css', [AssetOption::MINIFY => false]);
        $this->assertSame(false, $asset->isValid());

    }

    public function testHttpScheme()
    {
        $_SERVER['HTTP_SCHEME'] = 'https';
        $asset = new Asset(AssetType::CSS, '//test.com');
        $this->assertSame('https://test.com', $asset->getPath());
        unset($_SERVER['HTTP_SCHEME']);
    }

    public function testHttpSchemeFailHttps()
    {
        $asset = new Asset(AssetType::CSS, '//test.com');
        $this->assertSame('http://test.com', $asset->getPath());
    }

    public function testHttps()
    {
        unset($_SERVER['HTTP_SCHEME']);
        $_SERVER['HTTPS'] = 'on';
        $asset = new Asset(AssetType::CSS, '//test.com');
        $this->assertSame('https://test.com', $asset->getPath());
        unset($_SERVER['HTTPS']);
    }

    public function testHttpsFail()
    {
        unset($_SERVER['HTTP_SCHEME']);
        $_SERVER['HTTPS'] = 'off';
        $asset = new Asset(AssetType::CSS, '//test.com');
        $this->assertSame('http://test.com', $asset->getPath());
        unset($_SERVER['HTTPS']);
    }

    public function testUrl()
    {
        $asset = new Asset(AssetType::CSS, 'url:/test.js');
        $this->assertSame('/test.js', $asset->getPath());
    }

    public function testLocal()
    {
        $asset = new Asset(AssetType::CSS, 'local:/test.js');
        $this->assertSame('/test.js', $asset->getPath());
    }


    public function testServerPort()
    {
        $_SERVER['SERVER_PORT'] = 443;
        $asset = new Asset(AssetType::CSS, '//test.com');
        $this->assertSame('https://test.com', $asset->getPath());
        unset($_SERVER['SERVER_PORT']);
    }

    public function testServerPortFail()
    {
        unset($_SERVER['HTTP_SCHEME']);
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = 4333;
        $asset = new Asset(AssetType::CSS, '//test.com');
        $this->assertSame(true, $asset->isUrl());
        $this->assertSame('http://test.com', $asset->getPath());
        unset($_SERVER['SERVER_PORT']);
    }

    public function testGetId()
    {
        $asset = new Asset(AssetType::CSS, '//test.com', [AssetOption::MINIFY => false, AssetOption::NOT_COLLECT => true]);
        $this->assertSame('429771529c250691d1dfbcfe11291c91', $asset->getId());
    }

}
