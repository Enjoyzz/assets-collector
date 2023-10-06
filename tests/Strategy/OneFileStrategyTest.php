<?php

namespace Tests\Enjoys\AssetsCollector\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Strategy\OneFileStrategy;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Tests\Enjoys\AssetsCollector\ArrayLogger;
use Tests\Enjoys\AssetsCollector\HelpersTestTrait;
use tubalmartin\CssMin\Minifier as CSSmin;

class OneFileStrategyTest extends TestCase
{

    use HelpersTestTrait;

    private ?Environment $environment;
    private ?LoggerInterface $logger;


    protected function setUp(): void
    {
        $this->logger = new ArrayLogger();
        $this->environment = new Environment('tests/_compile', __DIR__ . '/..');
        $this->environment
            ->setBaseUrl('/test/something')
            ->setLogger($this->logger)
            ->setMinifier(AssetType::CSS, function ($content) {
                $compressor = new CSSMin();
                return $compressor->run($content);
            });
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__ . '/../_compile', true);
        $this->removeDirectoryRecursive(__DIR__ . '/../tests', true);
        $this->environment = null;
        $this->logger = null;
    }

    /**
     * @throws \Exception
     */
    public function testOneFileStrategy(): void
    {

        $assetsCollection = [
            new Asset(AssetType::CSS, __DIR__ . '/../fixtures/test.css'),
            new Asset(AssetType::CSS, __DIR__ . '/../../tests/fixtures/test2.css'),
        ];
        $strategy = new OneFileStrategy();
        $result = $strategy->getAssets(AssetType::CSS, $assetsCollection, $this->environment);

        $this->assertSame(['/test/something/' . $strategy->getFilename()],
            array_map(function ($i) {
                return $i->getAttributeCollection()->get(AssetType::CSS->getSrcAttribute());
            }, $result, [])
        );


        $this->assertSame(
            str_replace(
                "\r",
                "",
                <<<CSS
body{color:#00008b}
p{color:#fff}

CSS
            ),
            file_get_contents($strategy->getFilePath())
        );

        $this->assertCount(9, $this->logger->getLog(LogLevel::INFO));

    }

    /**
     * @throws \Exception
     */
    public function testWithFilesCollectAndNoCollect()
    {

        $assetsCollection = [
            new Asset(AssetType::CSS, __DIR__ . '/../fixtures/test.css'),
            new Asset(AssetType::CSS, __DIR__ . '/../../tests/fixtures/test2.css'),
            new Asset(AssetType::CSS, __DIR__ . '/../../tests/fixtures/test3.css', [
                AssetOption::NOT_COLLECT => true,
            ]),
        ];
        $strategy = new OneFileStrategy();
        $result = $strategy->getAssets(AssetType::CSS, $assetsCollection, $this->environment);

        $this->assertSame([
            '/test/something/' . $strategy->getFilename(),
            '/test/something/fixtures/test3.css'
        ],
            array_map(function ($i) {
                return $i->getAttributeCollection()->get(AssetType::CSS->getSrcAttribute());
            }, $result)
        );
        $this->assertSame(
            str_replace(
                "\r",
                "",
                <<<CSS
body{color:#00008b}
p{color:#fff}

CSS
            ),
            file_get_contents($strategy->getFilePath())
        );
        $this->assertCount(13, $this->logger->getLog(LogLevel::INFO));

    }



}
