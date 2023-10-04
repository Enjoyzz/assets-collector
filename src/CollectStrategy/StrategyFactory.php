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
     * @param AssetType $type
     * @return StrategyInterface
     * @throws UnexpectedParameters
     */
    public static function getStrategy(
        Environment $environment,
        array $assetsCollection,
        AssetType $type
    ): StrategyInterface {
        $strategyClass = self::STRATEGY[$environment->getStrategy()] ?? throw new UnexpectedParameters(
            'Invalid strategy'
        );

        return new $strategyClass($environment, $assetsCollection, $type);
    }
}
