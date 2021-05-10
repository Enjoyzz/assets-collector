<?php

namespace Enjoys\AssetsCollector\CollectStrategy;

interface StrategyInterface
{
    /**
     * @return array
     */
    public function getResult(): array;
}
