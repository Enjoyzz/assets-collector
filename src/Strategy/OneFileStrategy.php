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
    private int $cacheTime = 0;
    private string $filename = '';
    private string $filePath = '';
    private string $fileUrl = '';

    /**
     * @param Asset[] $assets
     * @return string
     */
    private function generateHashId(array $assets): string
    {

        $assetsIds = array_map(fn($i) => $i->getId(), $assets);
        sort($assetsIds);
        return md5(implode('', $assetsIds));
    }


    /**
     * @psalm-suppress PossiblyFalseOperand
     */
    private function isCacheValid(): bool
    {
        if (file_exists($this->filePath)){
            return (filemtime($this->filePath) + $this->cacheTime) > time();
        }
        return false;
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

        $notCollectedResult = (new ManyFilesStrategy())->getAssets($type, $notCollectAssets, $environment);

        $result = array_merge([
            new Asset($type, $this->fileUrl, [
                AssetOption::ATTRIBUTES => [
                    $type->htmlAttribute() => $this->fileUrl
                ]
            ])
        ], $notCollectedResult);

        try {
            if ($this->isCacheValid()) {
                $logger->info(sprintf('Use cached file: %s', $this->filePath));
                $logger->info(sprintf('Return url: %s', $this->fileUrl));
                return $result;
            }

            foreach ($collectAssets as $asset) {

                writeFile(
                    $this->filePath,
                    (new Reader($asset, $environment))->replaceRelativeUrls()->minify()->getContents(),
                    'a'
                );

                foreach ($asset->getOptions()->getSymlinks() as $optLink => $optTarget) {
                    if (makeSymlink($optLink, $optTarget)) {
                        $logger->info(sprintf('Created symlink: %s', $optLink));
                    }
                }
            }

            $logger->info(sprintf('Write to: %s', $this->filePath));
        } catch (Exception $e) {
            $logger->notice($e->getMessage());
        }

        $logger->info(sprintf('Return url: %s', $this->fileUrl));

        return $result;
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
