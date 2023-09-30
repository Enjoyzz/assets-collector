<?php

namespace Enjoys\AssetsCollector\CollectStrategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\ManyFilesStrategy;
use Enjoys\AssetsCollector\CollectStrategy\Strategy\OneFileStrategy;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Exception\UnexpectedParameters;

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
     * @throws UnexpectedParameters
     */
    public static function getStrategy(
        Environment $environment,
        array $assetsCollection,
        AssetType|string $type
    ): StrategyInterface {
        if (is_string($type)){
            $type = AssetType::from($type);
        }
        
        $strategy = $environment->getStrategy();
        if (!isset(self::STRATEGY[$strategy])) {
            throw new UnexpectedParameters('Invalid strategy');
        }
        $strategyClass = self::STRATEGY[$strategy];

        return new $strategyClass($environment, $assetsCollection, $type);
    }
}
