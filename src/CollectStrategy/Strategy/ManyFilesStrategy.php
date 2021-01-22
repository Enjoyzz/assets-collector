<?php


namespace Enjoys\AssetsCollector\CollectStrategy\Strategy;


use Enjoys\AssetsCollector\CollectStrategy\StrategyAbstract;

class ManyFilesStrategy extends StrategyAbstract
{
    /**
     * @return array<string>
     */
    public function getResult(): array
    {
        $result = [];

        foreach ($this->assetsCollection as $asset) {

            if($asset->getPath() === false){
                continue;
            }

            if ($asset->isUrl()) {
                $result[] = $asset->getPath();
                continue;
            }

            $link = str_replace($this->environment->getProjectDir(), '', $asset->getPath());

            $symlink = $this->environment->getCompileDir() . $link;

            try {
                if (!file_exists($symlink)) {
                    $this->createDirectory(pathinfo($symlink, PATHINFO_DIRNAME));
                    symlink($asset->getPath(),  $symlink);
                    $this->logger->info(sprintf('Create symlink: %s', $symlink));
                }
            } catch (\Exception  $e) {
                $this->logger->error($e->getMessage());
            }

            $result[] = $this->environment->getBaseUrl() . $link;
        }
        return $result;
    }
}