<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\CollectStrategy\StrategyFactory;
use Enjoys\AssetsCollector\Render\RenderFactory;
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
     * @var Environment
     */
    private Environment $environment;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;

        $this->logger = $this->environment->getLogger();
        $this->assetsCollection = new AssetsCollection($this->environment);
    }

    /**
     * @param string $type
     * @param string|array<mixed> $paths
     * @param string $namespace
     * @return $this
     */
    public function add(string $type, $paths, $namespace = self::NAMESPACE_COMMON): Assets
    {
        foreach ((array)$paths as $path) {
            $params = [];
            if (is_array($path)) {
                $params = $path;
                $path = array_shift($params);
            }

            $this->assetsCollection->add(
                new Asset($type, $path, $params),
                $namespace
            );
        }
        return $this;
    }

    /**
     * @param string $type
     * @param string $namespace
     * @return string
     * @throws \Exception
     */
    public function get(string $type, $namespace = self::NAMESPACE_COMMON): string
    {
        $paths = $this->getResults($type, $this->assetsCollection->get($type, $namespace), $namespace);
        return RenderFactory::getRender(\strtolower($type), $this->environment)->getResult($paths);
    }


    /**
     * @param string $type
     * @param array<Asset> $assetsCollection
     * @param string $namespace
     * @return array<string>
     * @throws \Exception
     */
    private function getResults(string $type, array $assetsCollection, $namespace = self::NAMESPACE_COMMON): array
    {
        $strategy = StrategyFactory::getStrategy(
            $this->environment,
            $assetsCollection,
            $type,
            $namespace
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


}