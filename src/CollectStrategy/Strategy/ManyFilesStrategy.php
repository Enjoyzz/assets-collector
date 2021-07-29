<?php

namespace Enjoys\AssetsCollector\CollectStrategy\Strategy;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\CollectStrategy\StrategyAbstract;
use Enjoys\AssetsCollector\Helpers;

class ManyFilesStrategy extends StrategyAbstract
{
    /**
     * @return string[]
     */
    public function getResult(): array
    {
        $result = [];

        foreach ($this->assetsCollection as $asset) {
            if (false === $path = $asset->getPath()) {
                continue;
            }

            if ($asset->isUrl()) {
                $result[] = $path;
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
                Helpers::createSymlink($this->environment->getCompileDir() . $link, $path, $this->logger);

                $optSymlinks = (array)$asset->getOption(Asset::CREATE_SYMLINK, []);

                /** @var array<string, string> $optSymlinks */
                foreach ($optSymlinks as $optLink => $optTarget) {
                    Helpers::createSymlink($optLink, $optTarget, $this->logger);
                }
            } catch (\Exception  $e) {
                $this->logger->error($e->getMessage());
            }


            $result[] = $this->environment->getBaseUrl() . str_replace(DIRECTORY_SEPARATOR, '/', $link);
        }

        return $result;
    }
}
