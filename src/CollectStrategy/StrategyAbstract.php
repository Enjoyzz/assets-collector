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

    protected string $collectionHashId;

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
        $this->collectionHashId = $this->generateCollectionHashId();
        $this->type = $type;
        $this->logger = $environment->getLogger();
    }

    private function generateCollectionHashId(): string
    {
        $assetsIds = array_keys($this->assetsCollection);
        sort($assetsIds);
        return md5(implode('', $assetsIds));
    }

    public function getCollectionHashId(): string
    {
        return $this->collectionHashId;
    }

}
