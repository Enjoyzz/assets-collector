<?php

namespace Tests\Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetsCollection;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Strategy\ManyFilesStrategy;
use PHPUnit\Framework\TestCase;
use Tests\Enjoys\AssetsCollector\HelpersTestTrait;

class ManyFilesStrategyTest extends TestCase
{
    use HelpersTestTrait;

    /**
     * @var Environment
     */
    private ?Environment $environment;
    /**
     * @var AssetsCollection
     */
    private ?AssetsCollection $assetCollection;

    protected function setUp(): void
    {
        $this->environment = new Environment('_compile', __DIR__ . '/../..');
        $this->environment->setBaseUrl('/foo');
        $this->assetCollection = new AssetsCollection();
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__ . '/_compile', true);

        $this->environment = null;
        $this->assetCollection = null;
    }

    public function testManyFilesStrategy(): void
    {
        $this->assetCollection->add(new Asset(AssetType::CSS, '//google.com'), 'test');
        $this->assetCollection->add(new Asset(AssetType::CSS, '//yandex.ru'), 'test');
        $this->assetCollection->add(new Asset(AssetType::CSS, __DIR__ . '/../../fixtures/test.css'), 'test');

        $strategy = new ManyFilesStrategy();

        $this->assertSame(
            [
                'http://google.com',
                'http://yandex.ru',
                '/foo/fixtures/test.css',
            ],
            array_map(function ($i) {
                return $i->getAttributeCollection()->get(AssetType::CSS->getSrcAttribute());
            },
                $strategy->getAssets(
                    AssetType::CSS,
                    $this->assetCollection->get(AssetType::CSS, 'test'),
                    $this->environment,
                ),
                [])
        );
    }
}
