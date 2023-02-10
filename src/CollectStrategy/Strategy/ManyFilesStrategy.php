<?php

namespace Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\CollectStrategy\StrategyAbstract;
use Enjoys\AssetsCollector\Helpers;

class ManyFilesStrategy extends StrategyAbstract
{

    /**
     * @return array<string, array|null>
     */
    public function getResult(): array
    {
        $result = [];

        foreach ($this->assets as $asset) {
            if (false === $path = $asset->getPath()) {
                continue;
            }

            if ($asset->isUrl()) {
                $result[$path] = $asset->getOptions()->getAttributes();
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

            try {
                $asset->getOptions()->setOption(
                    Asset::CREATE_SYMLINK,
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


            $result[$this->environment->getBaseUrl() . str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                $link
            )] = $asset->getOptions()->getAttributes();
        }

        return $result;
    }
}
