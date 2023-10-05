<?php

namespace Tests\Enjoys\AssetsCollector\Strategy;

use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Environment;
use Tests\Enjoys\AssetsCollector\HelpersTestTrait;
use tubalmartin\CssMin\Minifier as CSSmin;

class OneFileStrategyTest //extends TestCase
{

    use HelpersTestTrait;

    /**
     * @var Environment
     */
    private ?Environment $environment;


    protected function setUp(): void
    {
        $this->environment = new Environment('_compile', __DIR__ . '/../..');
        $this->environment->setBaseUrl('/test/something');
        $this->environment->setMinifier(AssetType::CSS, function ($content) {
            $compressor = new CSSMin();
            return $compressor->run($content);
        });
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__ . '/_compile', true);
        $this->environment = null;
    }

//    public function testOneFileStrategy()
//    {
//        $this->environment->setStrategy(OneFileStrategy::class);
//
//        $assetsCollection = [
//            new Asset(AssetType::CSS, __DIR__ . '/../../fixtures/test.css'),
//            new Asset(AssetType::CSS, __DIR__ . '/../../../tests/fixtures/test2.css'),
//        ];
//        $strategy = new OneFileStrategy();
//
//        $this->assertSame(['/test/something/_css/' . $strategy->getHashId() . '.css'],
//            array_map(function ($i) {
//                return $i->getAttributeCollection()->get(AssetType::CSS->getSrcAttribute());
//            }, $strategy->getAssets(AssetType::CSS, $assetsCollection, $this->environment), [])
//        );
//
//
//        $this->assertSame(
//            str_replace(
//                "\r",
//                "",
//                <<<CSS
//body{color:#00008b}
//p{color:#fff}
//
//CSS
//            ),
//            file_get_contents($strategy->getFilePath())
//        );
//    }

//    public function testWithFilesCollectAndNoCollect()
//    {
//        $this->environment->setStrategy(Assets::STRATEGY_ONE_FILE);
//
//        $assetsCollection = [
//            new Asset(AssetType::CSS, __DIR__ . '/../../fixtures/test.css'),
//            new Asset(AssetType::CSS, __DIR__ . '/../../../tests/fixtures/test2.css'),
//            new Asset(AssetType::CSS, __DIR__ . '/../../../tests/fixtures/test3.css', [
//                AssetOption::NOT_COLLECT => true,
//            ]),
//        ];
//        $strategy = new OneFileStrategy($this->environment, $assetsCollection, AssetType::CSS);
//
//        $this->assertSame([
//            '/test/something/_css/' . $strategy->getHashId() . '.css',
//            '/test/something/fixtures/test3.css'
//        ],
//            array_map(function ($i) {
//                return $i->getAttributeCollection()->get(AssetType::CSS->getSrcAttribute());
//            }, $strategy->getResult())
//        );
//        $this->assertSame(
//            str_replace(
//                "\r",
//                "",
//                <<<CSS
//body{color:#00008b}
//p{color:#fff}
//
//CSS
//            ),
//            file_get_contents($strategy->getFilePath())
//        );
//    }

}
