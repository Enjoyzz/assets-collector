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

    public function add(Asset $asset, string $group): void
    {
        if (!$asset->isValid()) {
            $this->logger->notice(sprintf('Path invalid: %s', $asset->getOrigPath()));
            return;
        }

        if ($this->has($asset, $group)) {
            $this->logger->notice(sprintf('Duplicate path: %s', $asset->getOrigPath()));
            return;
        }


        $this->assets[$asset->getType()->value][$group][$asset->getId()] = $asset;
    }

    public function has(Asset $asset, string $group): bool
    {
        if (isset($this->assets[$asset->getType()->value][$group][$asset->getId()])) {
            return true;
        }
        return false;
    }

    /**
     * @param AssetType $type
     * @param string $group
     * @return Asset[]
     */
    public function get(AssetType $type, string $group): array
    {
        if (!isset($this->assets[$type->value][$group])) {
            return [];
        }
        return $this->assets[$type->value][$group];
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
