<?php

namespace Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\AttributeCollection;
use Enjoys\AssetsCollector\CollectStrategy\StrategyAbstract;
use Enjoys\AssetsCollector\Content\Reader;
use Enjoys\AssetsCollector\Helpers;

class ManyFilesStrategy extends StrategyAbstract
{


    /**
     * @return array<string, Asset>
     * @throws \Exception
     */
    public function getResult(): array
    {
        $cacheDir = $this->environment->getCompileDir() . '/.cache';

//        $result = [];

        foreach ($this->assets as $asset) {
            $path = $asset->getPath();
            $assetAttributeCollection = $asset->getAttributeCollection();

            if ($asset->isUrl()) {
                $assetAttributeCollection->set($this->type->getSrcAttribute(), $this->addVersion($path));
                continue;
            }

            $link = str_replace(
                [
                    $this->environment->getCompileDir(),
                    $this->environment->getProjectDir()
                ],
                '',
                $path
            );

            /**
             * TODO
             * @psalm-suppress PossiblyNullOperand
             */
            $cacheFile = $cacheDir . '/' . $asset->getId();
            if (!file_exists($cacheFile) || (filemtime($cacheFile) + $this->environment->getCacheTime()) < time()) {
                (new Reader($asset, $this->environment))->replaceRelativeUrlsAndCreatedSymlinks();
                Helpers::createEmptyFile($cacheFile, $this->logger);
            }

            try {
                $asset->getOptions()->setOption(
                    AssetOption::SYMLINKS,
                    array_merge(
                        [$this->environment->getCompileDir() . $link => $path],
                        $asset->getOptions()->getSymlinks()
                    )
                );

                foreach ($asset->getOptions()->getSymlinks() as $optLink => $optTarget) {
                    Helpers::createSymlink($optLink, $optTarget, $this->logger);
                }
            } catch (\Exception  $e) {
                $this->logger->error($e->getMessage());
            }


            $assetAttributeCollection->set($this->type->getSrcAttribute(),$this->addVersion(
                $this->environment->getBaseUrl() . str_replace(
                    DIRECTORY_SEPARATOR,
                    '/',
                    $link
                )
            ));
        }

        return $this->assets;
    }
}
