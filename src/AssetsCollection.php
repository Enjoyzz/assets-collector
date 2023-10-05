<?php

namespace Enjoys\AssetsCollector;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AssetsCollection
{
    /**
     * @var array<string, array<string, array<string, Asset>>>
     */
    private array $assets = [];


    public function __construct(private readonly LoggerInterface $logger = new NullLogger())
    {
    }

    public function add(Asset $asset, string $namespace): void
    {
        if (!$asset->isValid()) {
            $this->logger->notice(sprintf('Path invalid: %s', $asset->getOrigPath()));
            return;
        }

        if ($this->has($asset, $namespace)) {
            $this->logger->notice(sprintf('Duplicate path: %s', $asset->getOrigPath()));
            return;
        }


        $this->assets[$asset->getType()->value][$namespace][$asset->getId()] = $asset;
    }

    public function has(Asset $asset, string $namespace): bool
    {
        if (isset($this->assets[$asset->getType()->value][$namespace][$asset->getId()])) {
            return true;
        }
        return false;
    }

    /**
     * @param AssetType $type
     * @param string $namespace
     * @return Asset[]
     */
    public function get(AssetType $type, string $namespace): array
    {
        if (!isset($this->assets[$type->value][$namespace])) {
            return [];
        }
        return $this->assets[$type->value][$namespace];
    }

    /**
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function push(AssetsCollection $collection): void
    {
        $this->assets = array_merge_recursive_distinct($this->getAssets(), $collection->getAssets());
    }

    /**
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function unshift(AssetsCollection $collection): void
    {
        $this->assets = array_merge_recursive_distinct($collection->getAssets(), $this->getAssets());
    }


    /**
     * @return array<string, array<string, array<string, Asset>>>
     */
    public function getAssets(): array
    {
        return $this->assets;
    }
}
