<?php

namespace Enjoys\AssetsCollector\CollectStrategy;

use Enjoys\AssetsCollector\Asset;

interface StrategyInterface
{

    /**
     * @return Asset[]
     */
    public function getResult(): array;
}
