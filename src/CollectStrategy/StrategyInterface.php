<?php

namespace Enjoys\AssetsCollector\CollectStrategy;

interface StrategyInterface
{

    /**
     * @return array<string, array|null>
     */
    public function getResult(): array;
}
