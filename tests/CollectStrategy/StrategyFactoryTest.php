<?php

namespace Tests\Enjoys\AssetsCollector\CollectStrategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\ManyFilesStrategy;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\OneFileStrategy;
use Enjoys\AssetsCollector\CollectStrategy\StrategyFactory;
use Enjoys\AssetsCollector\CollectStrategy\StrategyInterface;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Exception\UnexpectedParameters;
use PHPUnit\Framework\TestCase;
use Tests\Enjoys\AssetsCollector\HelpersTestTrait;

class StrategyFactoryTest extends TestCase
{
    use HelpersTestTrait;

    private array $constructorEnvironmentArgs = ['_compile', __DIR__.'/..'];

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__.'/../_compile', true);
    }

    public function testStrategyFactoryGood()
    {
        $environment = $this->getMockBuilder(Environment::class)
            ->setConstructorArgs($this->constructorEnvironmentArgs)
            ->onlyMethods(['getStrategy'])
            ->getMock();

        $environment->expects($this->any())->method('getStrategy')->willReturn(Assets::STRATEGY_MANY_FILES);

        $factory = StrategyFactory::getStrategy(
            $environment,
            [new Asset(AssetType::CSS, '//test.com')],
            AssetType::CSS
        );
        $this->assertInstanceOf(StrategyInterface::class, $factory);
        $this->assertInstanceOf(ManyFilesStrategy::class, $factory);

        $environment = $this->getMockBuilder(Environment::class)
            ->setConstructorArgs($this->constructorEnvironmentArgs)
            ->onlyMethods(['getStrategy'])
            ->getMock();
        $environment->expects($this->any())->method('getStrategy')->willReturn(Assets::STRATEGY_ONE_FILE);

        $factory = StrategyFactory::getStrategy(
            $environment,
            [new Asset('css', '//test.com')],
            'css'
        );

        $this->assertInstanceOf(StrategyInterface::class, $factory);
        $this->assertInstanceOf(OneFileStrategy::class, $factory);
    }

    public function testStrategyFactoryFail()
    {
        $this->expectException(UnexpectedParameters::class);
        $environment = $this->getMockBuilder(Environment::class)
            ->setConstructorArgs($this->constructorEnvironmentArgs)
            ->onlyMethods(['getStrategy'])
            ->getMock();
        $environment->expects($this->any())->method('getStrategy')->willReturn(99);

        StrategyFactory::getStrategy(
            $environment,
            [new Asset('css', '//test.com')],
            AssetType::CSS
        );
    }
}
