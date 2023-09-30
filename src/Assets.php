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
     * @param string $type
     * @param string|array $paths
     * @param string $namespace
     * @param string $method
     * @return $this
     */
    public function add(AssetType|string $type, $paths, string $namespace = self::NAMESPACE_COMMON, string $method = 'push'): Assets
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
     * @param string $type
     * @param string $namespace
     * @return string
     * @throws \Exception
     */
    public function get(AssetType|string $type, string $namespace = self::NAMESPACE_COMMON): string
    {
        if (is_string($type)){
            $type = AssetType::from($type);
        }
        $paths = $this->getResults($type, $this->assetsCollection->get($type, $namespace));
//        return RenderFactory::getRender(\strtolower($type), $this->environment)->getResult($paths);
        return $this->getEnvironment()->getRenderer($type)->getResult($paths);
    }


    /**
     * @param string $type
     * @param array<Asset> $assetsCollection
     * @return array
     */
    private function getResults(AssetType $type, array $assetsCollection): array
    {
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
