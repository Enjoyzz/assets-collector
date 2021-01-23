<?php


namespace Enjoys\AssetsCollector\CollectStrategy;


use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use Psr\Log\LoggerInterface;

abstract class StrategyAbstract implements StrategyInterface
{
    /**
     * @var array<Asset>
     */
    protected array $assetsCollection;

    /**
     * @var string css|js
     */
    protected string $type;

    /**
     * @var Environment
     */
    protected Environment $environment;

    protected LoggerInterface $logger;

    protected string $namespace;

    /**
     * StrategyAbstract constructor.
     * @param Environment $environment
     * @param array<Asset> $assetsCollection
     * @param string $type
     * @param string $namespace
     */
    public function __construct(
        Environment $environment,
        array $assetsCollection,
        string $type,
        string $namespace = Assets::NAMESPACE_COMMON
    ) {
        $this->environment = $environment;
        $this->assetsCollection = $assetsCollection;
        $this->type = $type;
        $this->logger = $environment->getLogger();
        $this->namespace = $namespace;
    }

    /**
     * @return array<string>
     */
    abstract public function getResult(): array;

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $file
     * @param string $data
     * @param string $mode
     * @return void
     */
    protected function writeFile(string $file, string $data, string $mode = 'w'): void
    {
        $f = fopen($file, $mode);
        if ($f) {
            fwrite($f, $data);
            fclose($f);
        }
    }

    /**
     * @param string $path
     * @param int $permissions
     * @return void
     * @throws \Exception
     */
    protected function createDirectory(string $path, int $permissions = 0777): void
    {
        //Clear the most recent error
        error_clear_last();

        if (!is_dir($path)) {
            if (@mkdir($path, $permissions, true) === false) {
                /** @var string[] $error */
                $error = error_get_last();
                throw new \Exception(
                    sprintf("Не удалось создать директорию: %s! Причина: %s", $path, $error['message'])
                );
            }
            $this->logger->info(sprintf('Create directory %s', $path));
        }
    }
}