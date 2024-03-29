<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetsCollection;
use Enjoys\AssetsCollector\Environment;
use phpDocumentor\Reflection\Types\This;
use PHPUnit\Framework\TestCase;

class AssetsCollectionTest extends TestCase
{
    /**
     * @var Environment
     */
    private Environment $environment;

    protected function setUp(): void
    {
        $this->environment = new Environment(__DIR__ . '/_compile',  __DIR__ . '/../');
        $this->environment->setBaseUrl('/t');
    }

    public function testAdd()
    {
        $collection = new AssetsCollection($this->environment);
        $collection->add(new Asset('css', 'http://test.test/style.css'),  'common');
        $this->assertCount(1, $collection->get('css', 'common'));

        $collection->add(new Asset('css', 'http://test.test/style.css'),  'common');
        $this->assertCount(1, $collection->get('css', 'common'));

//        $collection->add(new Asset('css', 'tests/fixtures/test.css', []),  'common');
//        $this->assertCount(2, $collection->get('css', 'common'));

        $collection->add(new Asset( 'css', __DIR__.'/fixtures/test.css'),  'common');
        $this->assertCount(2, $collection->get('css', 'common'));

        $collection->add(new Asset('css', __DIR__.'/fixtures/notexist.css'), 'common');
        $this->assertCount(2, $collection->get('css', 'common'));

        $this->assertSame([], $collection->get('css', 'empty_namespace'));
    }
}
