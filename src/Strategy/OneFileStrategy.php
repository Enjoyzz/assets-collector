<?php

namespace Enjoys\AssetsCollector\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Content\Reader;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Strategy;
use Exception;

use function Enjoys\FileSystem\createFile;
use function Enjoys\FileSystem\makeSymlink;
use function Enjoys\FileSystem\writeFile;

class OneFileStrategy implements Strategy
{
    private bool $fileCreated = false;
    private int $cacheTime = 0;
    private string $filename = '';
    private string $filePath = '';
    private string $fileUrl = '';

    private function generateHashId(array $assets): string
    {
        $assetsIds = array_keys($assets);
        sort($assetsIds);
        return md5(implode('', $assetsIds));
    }


    private function isCacheValid(string $filePath): bool
    {
        if ($this->fileCreated) {
            return false;
        }
        return (filemtime($filePath) + $this->cacheTime) > time();
    }


    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getAssets(AssetType $type, array $assetsCollection, Environment $environment): array
    {
        $this->cacheTime = $environment->getCacheTime();

        /** @infection-ignore-all */
        $this->filename = '_' . $type->value . DIRECTORY_SEPARATOR . $this->generateHashId(
                $assetsCollection
            ) . '.' . $type->value;

        /** @infection-ignore-all */
        $this->filePath = $environment->getCompileDir() . DIRECTORY_SEPARATOR . $this->filename;

        /** @infection-ignore-all */
        $this->fileUrl = $environment->getBaseUrl() . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $this->filename);


        $logger = $environment->getLogger();
        $notCollectAssets = array_filter($assetsCollection, function ($asset) {
            return $asset->getOptions()->isNotCollect();
        });

        $collectAssets = array_filter($assetsCollection, function ($asset) {
            return !$asset->getOptions()->isNotCollect();
        });

        if (!file_exists($this->filePath)) {
            createFile($this->filePath);
            $this->fileCreated = true;
            $logger->info(sprintf('Create file: %s', $this->filePath));
        }

        $notCollectedResult = (new ManyFilesStrategy())->getAssets($type, $notCollectAssets, $environment);

        try {
            if ($this->isCacheValid($this->filePath)) {
                $logger->info(sprintf('Use cached file: %s', $this->filePath));
                $logger->info(sprintf('Return url: %s', $this->fileUrl));
                return array_merge([
                    new Asset($type, $this->fileUrl, [
                        AssetOption::ATTRIBUTES => [
                            $type->getSrcAttribute() => $this->fileUrl
                        ]
                    ])
                ], $notCollectedResult);
            }

            $output = '';


            foreach ($collectAssets as $asset) {
                $reader = new Reader($asset, $environment);
                $output .= $reader->replaceRelativeUrlsAndCreatedSymlinks()->minify()->getContents();

                foreach ($asset->getOptions()->getSymlinks() as $optLink => $optTarget) {
                    if (makeSymlink($optLink, $optTarget)) {
                        $logger->info(sprintf('Created symlink: %s', $optLink));
                    }
                }
            }
            writeFile($this->filePath, $output);
            $logger->info(sprintf('Write to: %s', $this->filePath));
        } catch (Exception $e) {
            $logger->notice($e->getMessage());
        }

        $logger->info(sprintf('Return url: %s', $this->fileUrl));

        return array_merge([
            new Asset($type, $this->fileUrl, [
                AssetOption::ATTRIBUTES => [
                    $type->getSrcAttribute() => $this->fileUrl
                ]
            ])
        ], $notCollectedResult);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFileUrl(): string
    {
        return $this->fileUrl;
    }
}
