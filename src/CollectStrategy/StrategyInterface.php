<?php

namespace Enjoys\AssetsCollector\CollectStrategy;

interface StrategyInterface
{
    /**
     * @return array<string>
     */
    public function getResult(): array;
}
