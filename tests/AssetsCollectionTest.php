<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetsCollection;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Environment;
use phpDocumentor\Reflection\Types\This;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class AssetsCollectionTest extends TestCase
{

    use HelpersTestTrait;


    /**
     * @var Environment
     */
    private Environment $environment;

    protected function setUp(): void
    {
        $this->environment = new Environment(__DIR__ . '/_compile',  __DIR__ . '/../');
        $this->environment->setBaseUrl('/t');
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__ . '/_compile', true);
        $this->removeDirectoryRecursive(__DIR__ . '/tests', true);
    }

    public function testAdd()
    {
        $collection = new AssetsCollection();
        $collection->add(new Asset(AssetType::CSS, 'http://test.test/style.css'),  'common');
        $this->assertCount(1, $collection->get(AssetType::CSS, 'common'));

        $collection->add(new Asset(AssetType::CSS, 'http://test.test/style.css'),  'common');
        $this->assertCount(1, $collection->get(AssetType::CSS, 'common'));

//        $collection->add(new Asset('css', 'tests/fixtures/test.css', []),  'common');
//        $this->assertCount(2, $collection->get('css', 'common'));

        $collection->add(new Asset( AssetType::CSS, __DIR__.'/fixtures/test.css'),  'common');
        $this->assertCount(2, $collection->get(AssetType::CSS, 'common'));

        $collection->add(new Asset(AssetType::CSS, __DIR__.'/fixtures/notexist.css'), 'common');
        $this->assertCount(2, $collection->get(AssetType::CSS, 'common'));

        $this->assertSame([], $collection->get(AssetType::CSS, 'empty_namespace'));
    }

    public function testAddWithLogger()
    {
        $collection = new AssetsCollection($logger = new ArrayLogger());
        $collection->add(new Asset(AssetType::CSS, '//test.test/style.css'), 'main');
        $this->assertCount(0, $logger->getLog(LogLevel::NOTICE));

        $collection->add(new Asset(AssetType::CSS, '//test.test/style.css'), 'main');
        $this->assertCount(1, $logger->getLog(LogLevel::NOTICE));

        $collection->add(new Asset(AssetType::CSS, 'notexist.css'), 'main');
        $this->assertCount(2, $logger->getLog(LogLevel::NOTICE));
    }
}
