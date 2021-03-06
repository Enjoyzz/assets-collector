<?php

namespace Tests\Enjoys\AssetsCollector\CollectStrategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\ManyFilesStrategy;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\OneFileStrategy;
use Enjoys\AssetsCollector\CollectStrategy\StrategyFactory;
use Enjoys\AssetsCollector\CollectStrategy\StrategyInterface;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;

class StrategyFactoryTest extends TestCase
{

    public function testStrategyFactoryGood()
    {
        $environment = $this->getMockBuilder(Environment::class)
            ->onlyMethods(['getStrategy'])
            ->getMock();

        $environment->expects($this->any())->method('getStrategy')->willReturn(Assets::STRATEGY_MANY_FILES);

        $factory = StrategyFactory::getStrategy(
            $environment,
            [new Asset('css', '//test.com')],
            'css'
        );
        $this->assertInstanceOf(StrategyInterface::class, $factory);
        $this->assertInstanceOf(ManyFilesStrategy::class, $factory);

        $environment = $this->getMockBuilder(Environment::class)
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
        $this->expectException(\Exception::class);
        $environment = $this->getMockBuilder(Environment::class)
            ->onlyMethods(['getStrategy'])
            ->getMock();
        $environment->expects($this->any())->method('getStrategy')->willReturn(99);

        StrategyFactory::getStrategy(
            $environment,
            [new Asset('css', '//test.com')],
            'css'
        );
    }
}
