<?php

namespace Tests\Enjoys\AssetsCollector\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetsCollection;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Strategy\ManyFilesStrategy;
use PHPUnit\Framework\TestCase;
use Tests\Enjoys\AssetsCollector\ArrayLogger;
use Tests\Enjoys\AssetsCollector\HelpersTestTrait;

class ManyFilesStrategyTest extends TestCase
{
    use HelpersTestTrait;


    private ?Environment $environment = null;

    private ?AssetsCollection $assetCollection = null;

    private ?ArrayLogger $logger = null;

    protected function setUp(): void
    {
        $this->logger = new ArrayLogger();
        $this->environment = new Environment(
            '_compile', __DIR__ . '/..', $this->logger
        );
        $this->environment->setBaseUrl('/foo');
        $this->assetCollection = new AssetsCollection();
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__ . '/../_compile', true);
        $this->removeDirectoryRecursive(__DIR__ . '/../tests', true);

        $this->environment = null;
        $this->assetCollection = null;
        $this->logger = null;
    }

    /**
     * @throws \Exception
     */
    public function testManyFilesStrategy(): void
    {
        $assets = [
            new Asset(AssetType::CSS, '//google.com'),
            new Asset(AssetType::CSS, 'invalid'),
            new Asset(AssetType::CSS, 'tests/fixtures/test.css'),
            new Asset(AssetType::CSS, '//yandex.ru'),
        ];

        $strategy = new ManyFilesStrategy();

        $this->assertSame(
            [
                'http://google.com',
                '/foo/fixtures/test.css',
                'http://yandex.ru',
            ],
            array_map(function ($i) {
                return $i->getAttributeCollection()->get(AssetType::CSS->getSrcAttribute());
            },
                $strategy->getAssets(
                    AssetType::CSS,
                    $assets,
                    $this->environment,
                ),
                [])
        );
    }
}
