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
     * @return Strategy
     * @throws UnexpectedParameters
     * @throws \ReflectionException
     */
    public static function getStrategy(
        Environment $environment,
        array $assetsCollection,
        AssetType $type
    ): Strategy {
        $strategyClass = self::STRATEGY[$environment->getStrategy()] ?? throw new UnexpectedParameters(
            'Invalid strategy'
        );

        $strategy = new $strategyClass($environment, $assetsCollection, $type);

        $reflection = (new \ReflectionClass($strategy));

        if ($reflection->getParentClass()->getName() !== Strategy::class) {
            throw new \RuntimeException(
                sprintf('%s must be extended from %s', $strategy::class, Strategy::class)
            );
        }
        return $strategy;
    }
}
