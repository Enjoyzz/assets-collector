<?php

namespace Tests\Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\OneFileStrategy;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;
use Tests\Enjoys\AssetsCollector\HelpersTest;

class OneFileStrategyTest extends TestCase
{

    use HelpersTest;
    /**
     * @var Environment
     */
    private ?Environment $environment;


    protected function setUp(): void
    {
        $this->environment = new Environment('_compile', __DIR__ . '/../..');
        $this->environment->setBaseUrl('/test/something');
    }

    protected function tearDown(): void
    {

        $this->removeDirectoryRecursive($this->environment->getCompileDir(), true);

        $this->environment = null;
    }

    public function testOneFileStrategy()
    {
        $this->environment->setStrategy(Assets::STRATEGY_ONE_FILE);
        $uniqname = uniqid('css');
        $this->environment->setCssBuildFile('cssDir/' . $uniqname . '.css');


        $strategy = new OneFileStrategy(
            $this->environment, [
            new Asset('css', __DIR__ . '/../../fixtures/test.css'),
            new Asset('css', __DIR__ . '/../../../tests/fixtures/test2.css'),
        ], 'css'
        );

        $this->assertSame(['/test/something/cssDir/'.$uniqname.'.css'], $strategy->getResult());
        $this->assertSame(
            str_replace(
                "\r",
                "",
                <<<CSS
body{color:#00008b}
p{color:#fff}

CSS
            ),
            file_get_contents($this->environment->getCompileDir() . '/' . $this->environment->getBuildFile('css'))
        );
    }

}
