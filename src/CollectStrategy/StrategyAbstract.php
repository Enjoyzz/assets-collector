<?php

namespace Enjoys\AssetsCollector\CollectStrategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Environment;
use Psr\Log\LoggerInterface;

abstract class StrategyAbstract implements StrategyInterface
{
    /**
     * @var array<Asset>
     */
    protected array $assetsCollection;

    /**
     * @var string css|js
     */
    protected string $type;

    /**
     * @var Environment
     */
    protected Environment $environment;

    protected LoggerInterface $logger;


    /**
     * StrategyAbstract constructor.
     * @param Environment $environment
     * @param array<Asset> $assetsCollection
     * @param string $type
     */
    public function __construct(
        Environment $environment,
        array $assetsCollection,
        string $type
    ) {
        $this->environment = $environment;
        $this->assetsCollection = $assetsCollection;
        $this->type = $type;
        $this->logger = $environment->getLogger();
    }

}
