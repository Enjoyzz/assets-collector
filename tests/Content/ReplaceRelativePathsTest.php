<?php

declare(strict_types=1);

namespace Tests\Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Content\ReplaceRelativePaths;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;

class ReplaceRelativePathsTest extends TestCase
{
    /**
     * @var Environment
     */
    private Environment $config;


    protected function setUp(): void
    {
        $this->config = new Environment(__DIR__ . '/_compile', __DIR__ . '/../');
        $this->config->setBaseUrl('/t')
            ->setStrategy(Assets::STRATEGY_MANY_FILES)
        ;
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
        $replaceClass = new ReplaceRelativePaths(
            $data,$path,
            $this->config
        );

        $this->assertSame($expect, $replaceClass->getContent());
    }
}
