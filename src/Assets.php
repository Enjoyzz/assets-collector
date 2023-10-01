<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\CollectStrategy\StrategyFactory;
use Enjoys\AssetsCollector\Exception\NotAllowedMethods;
use Psr\Log\LoggerInterface;

class Assets
{
    public const NAMESPACE_COMMON = 'common';

    public const RENDER_HTML = 'html';

    public const STRATEGY_ONE_FILE = 0;
    public const STRATEGY_MANY_FILES = 1;

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
     * @param AssetType|string $type
     * @param array|string $paths
     * @param string $namespace
     * @param string $method
     * @return $this
     */
    public function add(AssetType|string $type, array|string $paths, string $namespace = self::NAMESPACE_COMMON, string $method = 'push'): Assets
    {
        $type = AssetType::normalize($type);

        $collection = new AssetsCollection($this->environment);
        /** @var array|string $path */
        foreach ((array)$paths as $path) {
            $params = [];
            if (is_array($path)) {
                $params = $path;

                /** @var string $path */
                $path = array_shift($params);
            }

            /** @var array<string, array|bool|null|string> $params */
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
     * @param AssetType|string $type
     * @param string $namespace
     * @return string
     */
    public function get(AssetType|string $type, string $namespace = self::NAMESPACE_COMMON): string
    {
        $type = AssetType::normalize($type);

        $paths = $this->getResults($type, $this->assetsCollection->get($type, $namespace));
//        return RenderFactory::getRender(\strtolower($type), $this->environment)->getResult($paths);
        return $this->getEnvironment()->getRenderer($type)->getResult($paths);
    }


    /**
     * @param AssetType|string $type
     * @param array<Asset> $assetsCollection
     * @return array
     */
    private function getResults(AssetType|string $type, array $assetsCollection): array
    {
        $type = AssetType::normalize($type);

        $strategy = StrategyFactory::getStrategy(
            $this->environment,
            $assetsCollection,
            $type
        );

        return $strategy->getResult();
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
