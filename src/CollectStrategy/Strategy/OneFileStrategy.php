<?php

namespace Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\CollectStrategy\StrategyAbstract;
use Enjoys\AssetsCollector\Content\Reader;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Helpers;

class OneFileStrategy extends StrategyAbstract
{
    private int $cacheTime;
    private string $filePath;
    private string $fileUrl;
    private bool $fileCreated = false;

    /**
     * @var Asset[]
     */
    private array $notCollect = [];

    /**
     * Build constructor.
     * @param Environment $environment
     * @param array<Asset> $assets
     * @param string $type
     * @throws \Exception
     */
    public function __construct(
        Environment $environment,
        array $assets,
        string $type
    ) {
        parent::__construct($environment, $assets, $type);

        $this->cacheTime = $environment->getCacheTime();

        $filename = $this->generateFilename($type);

        $this->filePath = $environment->getCompileDir() . DIRECTORY_SEPARATOR . $filename;
        $this->fileUrl = $environment->getBaseUrl() . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $filename);

        $this->notCollect = array_filter($assets, function ($asset){
            /** @var Asset $asset */
            return $asset->isNotCollect();
        });

        $this->assets = array_filter($assets, function ($asset){
            /** @var Asset $asset */
            return !$asset->isNotCollect();
        });


        $this->init();

    }

    /**
     * @param string $type css|js
     * @return string
     */
    private function generateFilename(string $type): string
    {
        return '_' . $type . DIRECTORY_SEPARATOR . $this->getHashId(). '.' . $type;
    }

    /**
     * @throws \Exception
     */
    private function init(): void
    {
        Helpers::createDirectory(pathinfo($this->filePath, PATHINFO_DIRNAME), $this->environment->getDirectoryPermissions(), $this->logger);

        if (!file_exists($this->filePath)) {
            Helpers::createEmptyFile($this->filePath, $this->logger);
            $this->fileCreated = true;
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
     * @return array<string, array|null>
     */
    public function getResult(): array
    {
        $notCollectedResult = (new ManyFilesStrategy($this->environment, $this->notCollect, $this->type))->getResult();

        try {
            if ($this->isCacheValid()) {
                $this->logger->info(sprintf('Use cached file: %s', $this->filePath));
                $this->logger->info(sprintf('Return url: %s', $this->fileUrl));
                return array_merge([$this->fileUrl => null], $notCollectedResult);
            }

            $output = '';

            foreach ($this->assets as $asset) {
                $output .= (new Reader($asset, $this->environment, $this->logger))->getContents();

                $optSymlinks = (array)$asset->getOptions()->getOption(Asset::CREATE_SYMLINK, []);

                /** @var array<string, string> $optSymlinks */
                foreach ($optSymlinks as $optLink => $optTarget) {
                    Helpers::createSymlink($optLink, $optTarget, $this->logger);
                }
            }
            Helpers::writeFile($this->filePath, $output, 'w', $this->logger);
        } catch (\Exception $e) {
            $this->logger->notice($e->getMessage());
        }

        $this->logger->info(sprintf('Return url: %s', $this->fileUrl));
        return array_merge([$this->fileUrl => null], $notCollectedResult);
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
