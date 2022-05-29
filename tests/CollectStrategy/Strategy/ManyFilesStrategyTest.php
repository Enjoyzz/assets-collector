<?php

namespace Tests\Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetsCollection;
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
        $this->assetCollection->add(new Asset('css', '//google.com', []), 'test');
        $this->assetCollection->add(new Asset('css', '//yandex.ru', []), 'test');
        $this->assetCollection->add(new Asset('css', __DIR__ . '/../../fixtures/test.css', []), 'test');

        $strategy = new ManyFilesStrategy(
            $this->environment,
            $this->assetCollection->get('css', 'test'),
            'css'
        );

        $this->assertSame(
            [
                'http://google.com' => null,
                'http://yandex.ru' => null,
                '/foo/fixtures/test.css' => null
            ],
            $strategy->getResult()
        );
    }
}
