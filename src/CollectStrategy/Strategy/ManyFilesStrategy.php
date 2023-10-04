<?php

namespace Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\CollectStrategy\Strategy;
use Enjoys\AssetsCollector\Content\Reader;
use Exception;

use function Enjoys\FileSystem\createFile;
use function Enjoys\FileSystem\makeSymlink;

class ManyFilesStrategy extends Strategy
{


    /**
     * @return Asset[]
     * @throws Exception
     * @psalm-suppress PossiblyFalseArgument
     */
    public function getResult(): array
    {
        $cacheDir = $this->environment->getCompileDir() . '/.cache';

        foreach ($this->assets as $asset) {
            if (!$asset->isValid()) {
                continue;
            }

            $assetAttributeCollection = $asset->getAttributeCollection();

            if ($asset->isUrl()) {
                $assetAttributeCollection->set($this->type->getSrcAttribute(), $this->addVersion($asset->getPath()));
                continue;
            }

            $link = str_replace(
                [
                    $this->environment->getCompileDir(),
                    $this->environment->getProjectDir()
                ],
                '',
                $asset->getPath()
            );

            $cacheFile = $cacheDir . '/' . $asset->getId();
            if (!file_exists($cacheFile) || (filemtime($cacheFile) + $this->environment->getCacheTime()) < time()) {
                (new Reader($asset, $this->environment))->replaceRelativeUrlsAndCreatedSymlinks();
                createFile($cacheFile);
                $this->logger->info(sprintf('Create file: %s', $cacheFile));
            }

            try {
                $asset->getOptions()->setOption(
                    AssetOption::SYMLINKS,
                    array_merge(
                        [$this->environment->getCompileDir() . $link => $asset->getPath()],
                        $asset->getOptions()->getSymlinks()
                    )
                );

                foreach ($asset->getOptions()->getSymlinks() as $optLink => $optTarget) {
                    if (makeSymlink($optLink, $optTarget)) {
                        $this->logger->info(sprintf('Created symlink: %s', $optLink));
                    }
                }
            } catch (Exception  $e) {
                $this->logger->error($e->getMessage());
            }


            $assetAttributeCollection->set(
                $this->type->getSrcAttribute(),
                $this->addVersion(
                    $this->environment->getBaseUrl() . str_replace(
                        DIRECTORY_SEPARATOR,
                        '/',
                        $link
                    )
                )
            );
        }

        return array_filter($this->assets, function (Asset $asset) {
            return $asset->isValid();
        });
    }
}
