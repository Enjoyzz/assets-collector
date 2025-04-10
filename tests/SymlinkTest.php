<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Strategy\ManyFilesStrategy;
use Enjoys\AssetsCollector\Strategy\OneFileStrategy;
use Enjoys\UrlConverter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class SymlinkTest extends TestCase
{
    use HelpersTestTrait;

    /**
     * @var Environment
     */
    private ?Environment $config;
    private ArrayLogger $logger;


    protected function setUp(): void
    {
        $this->logger = new ArrayLogger();
        $this->config = new Environment(__DIR__ . '/_compile', __DIR__ . '/../', $this->logger);
        $this->removeDirectoryRecursive(__DIR__ . '/_compile', true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__ . '/_compile', true);
        $this->removeDirectoryRecursive(__DIR__ . '/tests', true);
        $this->config = null;
        $this->logger = new ArrayLogger();
    }

    public function testSingleStrategyCreatedSymLinks()
    {
        $this->config->setStrategy(OneFileStrategy::class)->setBaseUrl('/_c');
        $assets = new Assets($this->config);
        $assets->add(
            AssetType::CSS,
            [
                $baseUrl = __DIR__ . '/../tests/fixtures/sub/css/style.css',
            ]
        );
        $assets->get(AssetType::CSS);

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

        $logs = array_values(array_filter($this->logger->getLog(LogLevel::INFO), function ($item){
            return str_starts_with($item[0], 'Created symlink:');
        }));

        $this->assertCount(2, $logs);

    }


    public function testManyStrategyCreatedSymLinks()
    {
        $this->config->setStrategy(ManyFilesStrategy::class);
        $this->config->setLogger($logger = new ArrayLogger());
        $this->config->setCacheTime(300);
        $assets = new Assets($this->config);
        $assets->add(
            AssetType::CSS,
            $targets = [
                $baseUrl = __DIR__ . '/fixtures/sub/css/style.css',
               __DIR__ . '/fixtures/test.css',
            ]
        );
        $assets->get(AssetType::CSS);

        $this->assertCount(10, $logger->getLog(LogLevel::INFO));

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

        $assets->get(AssetType::CSS);
        $this->assertCount(10, $logger->getLog(LogLevel::INFO));

    }

}
