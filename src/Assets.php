<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Exception\NotAllowedMethods;
use Psr\Log\LoggerInterface;

class Assets
{
    public const NAMESPACE_COMMON = 'common';

    /*
     * @var AssetsCollection
     */
    private AssetsCollection $assetsCollection;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(private Environment $environment)
    {
        $this->logger = $this->environment->getLogger();
        $this->assetsCollection = new AssetsCollection($this->environment);
    }

    /**
     * @param AssetType $type
     * @param array|string $paths
     * @param string $namespace
     * @param string $method
     * @return $this
     */
    public function add(
        AssetType $type,
        array|string $paths,
        string $namespace = self::NAMESPACE_COMMON,
        string $method = 'push'
    ): Assets {
        $collection = new AssetsCollection($this->environment);
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
                $namespace
            );
        }

        if (!in_array($method, ['push', 'unshift'], true)) {
            throw new NotAllowedMethods('Allowed methods only `push` and `unshift`');
        }

        $this->assetsCollection->$method($collection);

        return $this;
    }

    /**
     * @param AssetType $type
     * @param string $namespace
     * @return string
     */
    public function get(AssetType $type, string $namespace = self::NAMESPACE_COMMON): string
    {
        return $this->environment->getRenderer($type)->render(
            $this->environment->getStrategy()->getAssets(
                $type,
                $this->assetsCollection->get($type, $namespace),
                $this->environment
            )
        );
    }

    /**
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
