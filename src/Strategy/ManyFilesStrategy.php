<?php

namespace Enjoys\AssetsCollector\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Content\Reader;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Helpers;
use Enjoys\AssetsCollector\Strategy;
use Exception;

use function Enjoys\FileSystem\createFile;
use function Enjoys\FileSystem\makeSymlink;

class ManyFilesStrategy implements Strategy
{


    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getAssets(AssetType $type, array $assetsCollection, Environment $environment): array
    {
        /** @infection-ignore-all */
        $cacheDir = $environment->getCompileDir() . '/.cache';

        $logger = $environment->getLogger();

        foreach ($assetsCollection as $asset) {
            if (!$asset->isValid()) {
                continue;
            }

            $assetAttributeCollection = $asset->getAttributeCollection();

            if ($asset->isUrl()) {
                $assetAttributeCollection->set(
                    $type->getSrcAttribute(),
                    Helpers::addVersionToPath($asset->getPath(), $environment->getVersionQuery())
                );
                continue;
            }

            $link = str_replace(
                [
                    $environment->getCompileDir(),
                    $environment->getProjectDir()
                ],
                '',
                $asset->getPath()
            );

            $cacheFile = $cacheDir . '/' . $asset->getId();
            if (!file_exists($cacheFile) || (filemtime($cacheFile) + $environment->getCacheTime()) < time()) {
                (new Reader($asset, $environment))->replaceRelativeUrlsAndCreatedSymlinks();
                createFile($cacheFile);
                $logger->info(sprintf('Create file: %s', $cacheFile));
            }

            try {
                $asset->getOptions()->setOption(
                    AssetOption::SYMLINKS,
                    array_merge(
                        [$environment->getCompileDir() . $link => $asset->getPath()],
                        $asset->getOptions()->getSymlinks()
                    )
                );

                foreach ($asset->getOptions()->getSymlinks() as $optLink => $optTarget) {
                    if (makeSymlink($optLink, $optTarget)) {
                        $logger->info(sprintf('Created symlink: %s', $optLink));
                    }
                }
            } catch (Exception  $e) {
                $logger->error($e->getMessage());
            }


            $assetAttributeCollection->set(
                $type->getSrcAttribute(),
                Helpers::addVersionToPath(
                    $environment->getBaseUrl() . str_replace(
                        DIRECTORY_SEPARATOR,
                        '/',
                        $link
                    ),
                    $environment->getVersionQuery()
                )
            );
        }

        return array_filter($assetsCollection, function (Asset $asset) {
            return $asset->isValid();
        });
    }


}
