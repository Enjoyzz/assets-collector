<?php

namespace Tests\Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetsCollection;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\ManyFilesStrategy;
use Enjoys\AssetsCollector\Environment;
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
        $this->assetCollection = new AssetsCollection($this->environment);
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive($this->environment->getCompileDir(), true);

        $this->environment = null;
        $this->assetCollection = null;
    }

    public function testManyFilesStrategy(): void
    {
        $this->assetCollection->add(new Asset(AssetType::CSS, '//google.com'), 'test');
        $this->assetCollection->add(new Asset(AssetType::CSS, '//yandex.ru'), 'test');
        $this->assetCollection->add(new Asset(AssetType::CSS, __DIR__ . '/../../fixtures/test.css'), 'test');

        $strategy = new ManyFilesStrategy(
            $this->environment,
            $this->assetCollection->get('css', 'test'),
            AssetType::CSS
        );

        $this->assertSame(
            [
                'http://google.com',
                'http://yandex.ru',
                '/foo/fixtures/test.css',
            ],
            array_map(function ($i){
                return $i->getAttributeCollection()->get(AssetType::CSS->getSrcAttribute());
            }, $strategy->getResult(), [])
        );
    }
}
