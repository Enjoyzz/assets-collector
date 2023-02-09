<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use Enjoys\UrlConverter;
use PHPUnit\Framework\TestCase;

class SymlinkTest extends TestCase
{
    use HelpersTestTrait;

    /**
     * @var Environment
     */
    private ?Environment $config;


    protected function setUp(): void
    {
        $this->config = new Environment(__DIR__ . '/_compile', __DIR__ . '/../');
        $this->config->setLogger(new ArrayLogger());
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive($this->config->getCompileDir(), true);

        $this->config = null;
    }

    public function testSingleStrategyCreatedSymLinks()
    {
        $this->config->setStrategy(Assets::STRATEGY_ONE_FILE)->setBaseUrl('/_c');
        $assets = new Assets($this->config);
        $assets->add(
            'css',
            [
                $baseUrl = __DIR__ . '/../tests/fixtures/sub/css/style.css',
            ]
        );
        $assets->get('css');

        $urlConverter = new UrlConverter();
        $link1 = $urlConverter->relativeToAbsolute($baseUrl, '../fonts/font.eot?d7yf1v');
        $link2 = $urlConverter->relativeToAbsolute($baseUrl, './font2.eot');

        $this->assertSame($link1, readlink($this->config->getCompileDir().'/tests/fixtures/sub/fonts/font.eot'));
        $this->assertSame($link2, readlink($this->config->getCompileDir().'/tests/fixtures/sub/css/font2.eot'));

    }


//    public function _testManyStrategyCreatedSymLinks()
//    {
//        $this->config->setStrategy(Assets::STRATEGY_MANY_FILES);
//        $assets = new Assets($this->config);
//        $assets->add(
//            'css',
//            [
//                $baseUrl = './fixtures/sub/css/style.css',
//                __DIR__.'/fixtures/test.css',
//            ]
//        );
//        $assets->get('css');
//
//        $urlConverter = new UrlConverter();
//        $link1 = $urlConverter->relativeToAbsolute($baseUrl, '../fonts/font.eot?d7yf1v');
//        $link2 = $urlConverter->relativeToAbsolute($baseUrl, './font2.eot');
//
//        $this->assertSame($link1, readlink($this->config->getCompileDir().'/tests/fixtures/sub/fonts/font.eot'));
//        $this->assertSame($link2, readlink($this->config->getCompileDir().'/tests/fixtures/sub/css/font2.eot'));
//
//    }

}
