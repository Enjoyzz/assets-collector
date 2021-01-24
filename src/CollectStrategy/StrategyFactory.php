<?php

namespace Enjoys\AssetsCollector\CollectStrategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\ManyFilesStrategy;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\OneFileStrategy;
use Enjoys\AssetsCollector\Environment;

class StrategyFactory
{
    private const STRATEGY = [
        Assets::STRATEGY_ONE_FILE => OneFileStrategy::class,
        Assets::STRATEGY_MANY_FILES => ManyFilesStrategy::class
    ];


    /**
     * @param Environment $environment
     * @param array<Asset> $assetsCollection
     * @param string $type
     * @return StrategyInterface
     * @throws \Exception
     */
    public static function getStrategy(
        Environment $environment,
        array $assetsCollection,
        string $type
    ): StrategyInterface {
        $strategy = $environment->getStrategy();
        if (!isset(self::STRATEGY[$strategy])) {
            throw new \Exception('Invalid strategy');
        }
        $strategyClass = self::STRATEGY[$strategy];

        return new $strategyClass($environment, $assetsCollection, $type);
    }
}
