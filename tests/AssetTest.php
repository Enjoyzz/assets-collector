<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;

class AssetTest extends TestCase
{
    /**
     * @var Environment
     */
    private Environment $environment;

    protected function setUp(): void
    {
        $this->environment = new Environment(__DIR__ . '/_compile', __DIR__ . '/../');
        $this->environment->setBaseUrl('/t');
    }

    /**
     * 'type', 'path', [], 'isUrl', 'getPath', 'isMinify', 'getType', 'setId?'
     */
    public function data(): array
    {
        return [
            ['css', '//test', [], true, 'http://test', true, 'css', true],
            ['css', 'http://test', [], true, 'http://test', true, 'css', true],
            ['css', 'https://test', [], true, 'https://test', true, 'css', true],
            ['css', __DIR__.'/fixtures/test.css', [], false, __DIR__.'/fixtures/test.css', true, 'css', true],
            ['css', __DIR__.'/../README.md', [], false, realpath(__DIR__.'/../README.md'), true, 'css', true],
            ['css', '../README.md', [], false, false, true, 'css', false],
            ['css', '../README.md', [Asset::PARAM_MINIFY => false], false, false, false, 'css', false],
        ];
    }

    /**
     * @dataProvider data
     */
    public function test__construct($type, $path, $params, $isUrl, $getPath, $isMinify, $getType, $setId)
    {
        $asset = new Asset($type, $path, $params);
        $this->assertSame($isUrl, $asset->isUrl());
        $this->assertSame($getPath, $asset->getPath());
        $this->assertSame($isMinify, $asset->isMinify());
        $this->assertSame($getType, $asset->getType());
        $this->assertSame($getType, $asset->getType());
        $this->assertSame($setId, !is_null($asset->getId()));
    }


}
