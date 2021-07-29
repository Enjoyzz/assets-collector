<?php

namespace Enjoys\AssetsCollector\CollectStrategy;

interface StrategyInterface
{
    /**
     * @return string[]
     */
    public function getResult(): array;
}
