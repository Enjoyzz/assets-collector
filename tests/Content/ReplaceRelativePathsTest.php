<?php

declare(strict_types=1);

namespace Tests\Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Content\ReplaceRelative;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;
use Tests\Enjoys\AssetsCollector\HelpersTestTrait;

class ReplaceRelativePathsTest extends TestCase
{
    use HelpersTestTrait;

    /**
     * @var Environment
     */
    private ?Environment $config;


    protected function setUp(): void
    {
        $this->config = new Environment('_compile', __DIR__ . '/..');
        $this->config->setBaseUrl('/t')
            ->setStrategy(Assets::STRATEGY_MANY_FILES)
        ;
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive($this->config->getCompileDir(), true);
        $this->config = null;
    }

    public function data()
    {
        return [
            [
                "src:url('../fonts/font.eot');",
                __DIR__ . '/../fixtures/sub/css/style.css',
                "src:url('/t/fixtures/sub/fonts/font.eot');"
            ],
            [
                "src:url('font2.eot');",
                __DIR__ . '/../fixtures/sub/css/style.css',
                "src:url('/t/fixtures/sub/css/font2.eot');"
            ],
            [
                "src:url('./font2.eot');",
                __DIR__ . '/../fixtures/sub/css/style.css',
                "src:url('/t/fixtures/sub/css/font2.eot');"
            ],
            [
                "src:url('./../fonts/font.eot');",
                __DIR__ . '/../fixtures/sub/css/style.css',
                "src:url('/t/fixtures/sub/fonts/font.eot');"
            ],
            [
                "src:url('/font3.eot');",
                __DIR__ . '/../fixtures/sub/css/style.css',
                "src:url('/font3.eot');"
            ],

        ];
    }

    /**
     * @dataProvider data
     */
    public function testGetContent($data, $path, $expect)
    {
        $replaceClass = new ReplaceRelative(
            $data,
            $path,
            new Asset('css', $path),
            $this->config
        );

        $this->assertSame($expect, $replaceClass->getContent());
    }
}
