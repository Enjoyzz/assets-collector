<?php

namespace Enjoys\AssetsCollector;

use Psr\Log\LoggerInterface;

class AssetsCollection
{
    /**
     * @var array
     */
    private array $assets = [];
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;


    public function __construct(Environment $environment)
    {
        $this->logger = $environment->getLogger();
    }

    public function add(Asset $asset, string $namespace): void
    {
        if ($asset->getPath() === false || null === $assetId = $asset->getId()) {
            $this->logger->notice(sprintf('Path invalid: %s', $asset->getOrigPath()));
            return;
        }

        if ($this->has($asset, $namespace)) {
            $this->logger->notice(sprintf('Duplicate path: %s', $asset->getOrigPath()));
            return;
        }


        $this->assets[$asset->getType()][$namespace][$assetId] = $asset;
    }

    public function has(Asset $asset, string $namespace): bool
    {
        if (isset($this->assets[$asset->getType()][$namespace][$asset->getId()])) {
            return true;
        }
        return false;
    }

    /**
     * @param string $type
     * @param string $namespace
     * @return array<Asset>
     */
    public function get(string $type, string $namespace): array
    {
        if (!isset($this->assets[$type][$namespace])) {
            return [];
        }
        return $this->assets[$type][$namespace];
    }

    public function push(AssetsCollection $collection): void
    {
        $this->assets =  array_merge_recursive_distinct($this->getAssets(), $collection->getAssets());
    }

    public function unshift(AssetsCollection $collection): void
    {
        $this->assets =  array_merge_recursive_distinct($collection->getAssets(), $this->getAssets());
    }

    public function getAssets(): array
    {
        return $this->assets;
    }
}
