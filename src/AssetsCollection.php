<?php

namespace Enjoys\AssetsCollector;

use Psr\Log\LoggerInterface;

class AssetsCollection
{
    /**
     * @var array<string, array<string, array<string, Asset>>>
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
        if ($asset->getPath() === false || (null === $assetId = $asset->getId())) {
            $this->logger->notice(sprintf('Path invalid: %s', $asset->getOrigPath()));
            return;
        }

        if ($this->has($asset, $namespace)) {
            $this->logger->notice(sprintf('Duplicate path: %s', $asset->getOrigPath()));
            return;
        }


        $this->assets[$asset->getType()->value][$namespace][$assetId] = $asset;
    }

    public function has(Asset $asset, string $namespace): bool
    {
        if (isset($this->assets[$asset->getType()->value][$namespace][$asset->getId()])) {
            return true;
        }
        return false;
    }

    /**
     * @param AssetType|string $type
     * @param string $namespace
     * @return Asset[]
     */
    public function get(AssetType|string $type, string $namespace): array
    {
        $type = AssetType::normalize($type);

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
