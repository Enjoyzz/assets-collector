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
        $this->removeDirectoryRecursive($this->config->getCompileDir(), true);
    }

    protected function tearDown(): void
    {
     //   $this->removeDirectoryRecursive($this->config->getCompileDir(), true);

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
        $targets = [
            $urlConverter->relativeToAbsolute($baseUrl, '../fonts/font.eot?d7yf1v'),
            $urlConverter->relativeToAbsolute($baseUrl, './font2.eot')
        ];

        foreach ($this->findAllSymlinks($this->config->getCompileDir()) as $link => $target) {
            $this->assertTrue(in_array($link, [
                $this->config->getCompileDir() . '/tests/fixtures/sub/fonts/font.eot',
                $this->config->getCompileDir() . '/tests/fixtures/sub/css/font2.eot',
            ], true));

            $this->assertTrue(in_array($target, $targets, true));
        }
    }


    public function testManyStrategyCreatedSymLinks()
    {
        $this->config->setStrategy(Assets::STRATEGY_MANY_FILES);
        $this->config->setLogger($logger = new ArrayLogger());
        $this->config->setCacheTime(300);
        $assets = new Assets($this->config);
        $assets->add(
            'css',
            $targets = [
                $baseUrl = __DIR__ . '/fixtures/sub/css/style.css',
               __DIR__ . '/fixtures/test.css',
            ]
        );
        $assets->get('css');

        $this->assertCount(10, $logger->getLog('info'));

        $symlinks = $this->findAllSymlinks($this->config->getCompileDir());

//        dd($symlinks);
        $this->assertCount(4, $symlinks);


        $urlConverter = new UrlConverter();
        $targets[] = $urlConverter->relativeToAbsolute($baseUrl, '../fonts/font.eot?d7yf1v');
        $targets[] = $urlConverter->relativeToAbsolute($baseUrl, './font2.eot');

        foreach ($symlinks as $link => $target) {
            $this->assertTrue(in_array($link, [
                $this->config->getCompileDir() . '/tests/fixtures/sub/css/style.css',
                $this->config->getCompileDir() . '/tests/fixtures/test.css',
                $this->config->getCompileDir() . '/tests/fixtures/sub/fonts/font.eot',
                $this->config->getCompileDir() . '/tests/fixtures/sub/css/font2.eot',
            ], true), sprintf('%s not found', $link));

            $this->assertTrue(in_array($target, $targets, true));
        }

        $assets->get('css');
        $this->assertCount(10, $logger->getLog('info'));

    }

}
