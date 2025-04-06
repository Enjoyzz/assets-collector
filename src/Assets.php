<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Exception\NotAllowedMethods;
use Psr\Log\LoggerInterface;

class Assets
{
    public const GROUP_COMMON = 'common';

    /*
     * @var AssetsCollection
     */
    private AssetsCollection $assetsCollection;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(private readonly Environment $environment)
    {
        $this->logger = $this->environment->getLogger();
        $this->assetsCollection = new AssetsCollection($this->logger);
    }

    /**
     * @param AssetType $type
     * @param array|string $paths
     * @param string $group
     * @param string $method
     * @return $this
     */
    public function add(
        AssetType $type,
        array|string $paths,
        string $group = self::GROUP_COMMON,
        string $method = 'push'
    ): Assets {
        $collection = new AssetsCollection($this->logger);
        /** @var array|string $path */
        foreach ((array)$paths as $path) {
            $params = [];
            if (is_array($path)) {
                $params = $path;

                /** @var string $path */
                $path = array_shift($params);
            }

            /** @var array<string, array|bool> $params */
            $collection->add(
                new Asset($type, $path, $params),
                $group
            );
        }

        if (!in_array($method, ['push', 'unshift'], true)) {
            throw new NotAllowedMethods('Allowed methods only `push` and `unshift`');
        }

        $this->assetsCollection->$method($collection);

        return $this;
    }

    public function get(AssetType $type, string $group = self::GROUP_COMMON): string
    {
        return $this->environment->getRenderer($type)->render(
            $this->environment->getStrategy()->getAssets(
                $type,
                $this->assetsCollection->get($type, $group),
                $this->environment
            )
        );
    }

    /**
     * @todo: maybe remove
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

}
