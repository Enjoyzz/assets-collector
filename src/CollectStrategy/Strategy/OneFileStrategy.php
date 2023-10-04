<?php

namespace Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\CollectStrategy\StrategyAbstract;
use Enjoys\AssetsCollector\Content\Reader;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Helpers;
use Exception;

use function Enjoys\FileSystem\createFile;
use function Enjoys\FileSystem\makeSymlink;

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
     * @param AssetType $type
     * @throws Exception
     */
    public function __construct(
        Environment $environment,
        array $assets,
        AssetType $type
    ) {
        parent::__construct($environment, $assets, $type);

        $this->cacheTime = $environment->getCacheTime();

        $filename = $this->generateFilename($type);

        $this->filePath = $environment->getCompileDir() . DIRECTORY_SEPARATOR . $filename;
        $this->fileUrl = $environment->getBaseUrl() . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $filename);

        $this->notCollect = array_filter($assets, function ($asset) {
            /** @var Asset $asset */
            return $asset->getOptions()->isNotCollect();
        });

        $this->assets = array_filter($assets, function ($asset) {
            /** @var Asset $asset */
            return !$asset->getOptions()->isNotCollect();
        });


        $this->init();
    }

    /**
     * @param AssetType $type css|js
     * @return string
     */
    private function generateFilename(AssetType $type): string
    {
        return '_' . $type->value . DIRECTORY_SEPARATOR . $this->getHashId() . '.' . $type->value;
    }

    /**
     * @throws Exception
     */
    private function init(): void
    {
        Helpers::createDirectory(
            pathinfo($this->filePath, PATHINFO_DIRNAME),
            $this->environment->getDirectoryPermissions(),
            $this->logger
        );

        if (!file_exists($this->filePath)) {
            createFile($this->filePath);
            $this->fileCreated = true;
            $this->logger->info(sprintf('Create file: %s', $this->filePath));
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
     * @return Asset[]
     * @throws Exception
     */
    public function getResult(): array
    {
        $notCollectedResult = (new ManyFilesStrategy($this->environment, $this->notCollect, $this->type))->getResult();

        try {
            if ($this->isCacheValid()) {
                $this->logger->info(sprintf('Use cached file: %s', $this->filePath));
                $this->logger->info(sprintf('Return url: %s', $this->fileUrl));
                return array_merge([
                    new Asset($this->type, $this->fileUrl, [
                        AssetOption::ATTRIBUTES => [
                            $this->type->getSrcAttribute() => $this->fileUrl
                        ]
                    ])
                ], $notCollectedResult);
            }

            $output = '';


            foreach ($this->assets as $asset) {
                $reader = new Reader($asset, $this->environment);
                $output .= $reader->replaceRelativeUrlsAndCreatedSymlinks()->minify()->getContents();


                foreach ($asset->getOptions()->getSymlinks() as $optLink => $optTarget) {
                    if (makeSymlink($optLink, $optTarget)) {
                        $this->logger->info(sprintf('Created symlink: %s', $optLink));
                    }
                }
            }
            Helpers::writeFile($this->filePath, $output, 'w', $this->logger);
        } catch (Exception $e) {
            $this->logger->notice($e->getMessage());
        }

        $this->logger->info(sprintf('Return url: %s', $this->fileUrl));

        return array_merge([
            new Asset($this->type, $this->fileUrl, [
                AssetOption::ATTRIBUTES => [
                    $this->type->getSrcAttribute() => $this->fileUrl
                ]
            ])
        ], $notCollectedResult);
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
