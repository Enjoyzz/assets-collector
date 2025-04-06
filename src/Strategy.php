<?php

namespace Enjoys\AssetsCollector;

interface Strategy
{
    /**
     * @param AssetType $type
     * @param Asset[] $assetsCollection
     * @param Environment $environment
     * @return Asset[]
     */
    public function getAssets(AssetType $type, array $assetsCollection, Environment $environment): array;
}
