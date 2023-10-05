<?php

namespace Enjoys\AssetsCollector\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Content\Reader;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Strategy;
use Exception;

use function Enjoys\FileSystem\createDirectory;
use function Enjoys\FileSystem\createFile;
use function Enjoys\FileSystem\makeSymlink;
use function Enjoys\FileSystem\writeFile;

class OneFileStrategy implements Strategy
{
    private bool $fileCreated = false;
    private int $cacheTime = 0;

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
        $filename = '_' . $type->value . DIRECTORY_SEPARATOR . $this->generateHashId(
                $assetsCollection
            ) . '.' . $type->value;
        $filePath = $environment->getCompileDir() . DIRECTORY_SEPARATOR . $filename;
        $fileUrl = $environment->getBaseUrl() . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $filename);


        $logger = $environment->getLogger();
        $notCollectAssets = array_filter($assetsCollection, function ($asset) {
            /** @var Asset $asset */
            return $asset->getOptions()->isNotCollect();
        });

        $collectAssets = array_filter($assetsCollection, function ($asset) {
            /** @var Asset $asset */
            return !$asset->getOptions()->isNotCollect();
        });

        if (createDirectory(
            $path = pathinfo($filePath, PATHINFO_DIRNAME),
            $environment->getDirectoryPermissions()
        )) {
            $logger->info(sprintf('Create directory %s', $path));
        }

        if (!file_exists($filePath)) {
            createFile($filePath);
            $this->fileCreated = true;
            $logger->info(sprintf('Create file: %s', $filePath));
        }

        $notCollectedResult = (new ManyFilesStrategy())->getAssets($type, $notCollectAssets, $environment);

        try {
            if ($this->isCacheValid($filePath)) {
                $logger->info(sprintf('Use cached file: %s', $filePath));
                $logger->info(sprintf('Return url: %s', $fileUrl));
                return array_merge([
                    new Asset($type, $fileUrl, [
                        AssetOption::ATTRIBUTES => [
                            $type->getSrcAttribute() => $fileUrl
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
            writeFile($filePath, $output);
            $logger->info(sprintf('Write to: %s', $filePath));
        } catch (Exception $e) {
            $logger->notice($e->getMessage());
        }

        $logger->info(sprintf('Return url: %s', $fileUrl));

        return array_merge([
            new Asset($type, $fileUrl, [
                AssetOption::ATTRIBUTES => [
                    $type->getSrcAttribute() => $fileUrl
                ]
            ])
        ], $notCollectedResult);
    }
}
