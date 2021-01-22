<?php

namespace Tests\Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetsCollection;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\ManyFilesStrategy;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;

class ManyFilesStrategyTest extends TestCase
{

    /**
     * @var Environment
     */
    private Environment $environment;
    /**
     * @var AssetsCollection
     */
    private AssetsCollection $assetCollection;

    protected function setUp(): void
    {
        $this->environment = new Environment('_compile', __DIR__.'/../..');
        $this->environment->setBaseUrl('/foo');
        $this->assetCollection = new AssetsCollection($this->environment);
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
                'http://google.com',
                'http://yandex.ru',
                '/foo/fixtures/test.css'
            ],
            $strategy->getResult()
        );
    }
}
