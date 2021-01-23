<?php

namespace Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\CollectStrategy\StrategyAbstract;
use Enjoys\AssetsCollector\Content\Reader;
use Enjoys\AssetsCollector\Environment;

class OneFileStrategy extends StrategyAbstract
{
    private int $cacheTime;
    private string $filePath;
    private string $fileUrl;
    private bool $fileCreated = false;

    /**
     * Build constructor.
     * @param Environment $environment
     * @param array<Asset> $assetsCollection
     * @param string $type
     * @param string $namespace
     * @throws \Exception
     */
    public function __construct(
        Environment $environment,
        array $assetsCollection,
        string $type,
        string $namespace = Assets::NAMESPACE_COMMON
    ) {
        parent::__construct($environment, $assetsCollection, $type, $namespace);

        $this->cacheTime = $environment->getCacheTime();

        $filename = $this->generateFilename(
            $environment->getPageId(),
            $type
        );

        $this->filePath = $environment->getCompileDir() . DIRECTORY_SEPARATOR . $filename;
        $this->fileUrl = $environment->getBaseUrl() . DIRECTORY_SEPARATOR . $filename;
        $this->init();
    }

    /**
     * @param string|null $pageId
     * @param string $type css|js
     * @return string
     */
    private function generateFilename(?string $pageId, string $type): string
    {
        $type = \strtolower($type);
        if ($pageId === null) {
            $pageId = '';
            if (isset($_SERVER['REQUEST_URI'])) {
                $pageId = $_SERVER['REQUEST_URI'];
            }
        }
        return '_' . $type . DIRECTORY_SEPARATOR . md5($this->getNamespace() . $pageId) . '.' . $type;
    }

    /**
     * @throws \Exception
     */
    private function init(): void
    {
        $this->createDirectory(pathinfo($this->filePath, PATHINFO_DIRNAME));
        if (!file_exists($this->filePath)) {
            $this->writeFile($this->filePath, '');
            $this->fileCreated = true;
            $this->logger->info(sprintf('Create new file %s', $this->filePath));
        }
    }

    private function isCacheValid(): bool
    {
        if ($this->fileCreated) {
            return false;
        }
        return (filemtime($this->filePath) + $this->cacheTime) > time();
    }

    /**
     * @return array<string>
     */
    public function getResult(): array
    {
        try {
            if ($this->isCacheValid()) {
                $this->logger->info(sprintf('Use cached file: %s', $this->filePath));
                $this->logger->info(sprintf('Return url: %s', $this->fileUrl));
                return [$this->fileUrl];
            }

            $output = '';

            foreach ($this->assetsCollection as $asset) {
                $output .= (new Reader($asset, $this->logger))->getContents();
            }

            $this->writeFile($this->filePath, $output);
            $this->logger->info(sprintf('Write to: %s', $this->filePath));
        } catch (\Exception $e) {
            $this->logger->notice($e->getMessage());
        }

        $this->logger->info(sprintf('Return url: %s', $this->fileUrl));
        return [$this->fileUrl];
    }

    /**
     * @return string
     */
    public function getFileUrl(): string
    {
        return $this->fileUrl;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }
}