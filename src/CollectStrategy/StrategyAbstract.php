<?php

namespace Enjoys\AssetsCollector\CollectStrategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Environment;
use GuzzleHttp\Psr7\Uri;
use Psr\Log\LoggerInterface;

abstract class StrategyAbstract implements StrategyInterface
{
    /**
     * @var Asset[]
     */
    protected array $assets;

    protected string $hashId;


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
        protected AssetType|string $type
    ) {
        if (is_string($type)) {
            $this->type = AssetType::from($type);
        }

        $this->environment = $environment;
        $this->assets = $assets;
        $this->hashId = $this->generateHashId();
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

    public function addVersion(string $path): string
    {
        $url = new Uri($path);
        parse_str($url->getQuery(), $query);
        return $url->withQuery(
            http_build_query(array_merge($query, $this->environment->getVersionQuery()))
        )->__toString();
    }

}
