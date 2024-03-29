<?php

namespace Enjoys\AssetsCollector\CollectStrategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Environment;
use Psr\Log\LoggerInterface;

abstract class StrategyAbstract implements StrategyInterface
{
    /**
     * @var Asset[]
     */
    protected array $assets;

    protected string $hashId;

    /**
     * @var string css or js
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
     * @param array<Asset> $assets
     * @param string $type
     */
    public function __construct(
        Environment $environment,
        array $assets,
        string $type
    ) {
        $this->environment = $environment;
        $this->assets = $assets;
        $this->hashId = $this->generateHashId();
        $this->type = $type;
        $this->logger = $environment->getLogger();
    }

    private function generateHashId(): string
    {
        $assetsIds = array_keys($this->assets);
        sort($assetsIds);
        return md5(implode('', $assetsIds));
    }

    public function getHashId(): string
    {
        return $this->hashId;
    }

}
