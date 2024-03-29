<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\Environment;
use Enjoys\UrlConverter;
use Psr\Log\LoggerInterface;

class ReplaceRelative
{
    private string $content;
    private LoggerInterface $logger;
    private Asset $asset;
    private Environment $environment;
    private string $path;

    public function __construct(string $content, string $path, Asset $asset, Environment $environment)
    {
        $this->content = $content;
        $this->asset = $asset;
        $this->environment = $environment;
        $this->path = $path;
        $this->logger = $environment->getLogger();
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getContent(): string
    {
        $result = preg_replace_callback(
            '/(url\([\'"]?)(?!["\'a-z]+:|[\'"]?\/{2})(.+?[^\'"])([\'"]?\))/i',
            function (array $m) {
                $normalizedPath = $this->getNormalizedPath($m[2]);
                if ($normalizedPath === false) {
                    return $m[1] . $m[2] . $m[3];
                }

                return $m[1] . $normalizedPath . $m[3];
            },
            $this->content
        );

        if ($result === null) {
            $this->logger->notice(
                sprintf('Regex return null value. Returned empty string: %s', $this->path)
            );
            return '';
        }
        $this->logger->info(sprintf('ReplaceRelativeUrls: %s', $this->path));
        return $result;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param string $relativePath
     * @return false|string
     * @throws \Exception
     */
    private function getNormalizedPath(string $relativePath)
    {
        if ($this->asset->isUrl()) {
            return $this->replaceUrls($this->path, $relativePath);
        }
        return $this->replacePath($this->path, $relativePath);
    }

    /**
     * @param string $baseUrl
     * @param string $relativeUrl
     * @return false|string
     */
    private function replaceUrls(string $baseUrl, string $relativeUrl)
    {
        $urlConverter = new UrlConverter();
        return $urlConverter->relativeToAbsolute($baseUrl, $relativeUrl);
    }

    /**
     * @param string $filePath
     * @param string $relativePath
     * @return false|string
     * @throws \Exception
     */
    private function replacePath(string $filePath, string $relativePath)
    {
        $realpath = realpath(
            pathinfo($filePath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR
            . parse_url($relativePath, PHP_URL_PATH)
        );

        if ($realpath === false) {
            return false;
        }

        $relativeFullPath = \str_replace(
            '\\',
            '/',
            \str_replace(
                [
                    $this->environment->getCompileDir(),
                    $this->environment->getProjectDir()
                ],
                '',
                $realpath
            )
        );

        $this->asset->getOptions()->setOption(
            AssetOption::SYMLINKS,
            array_merge(
                [$this->environment->getCompileDir() . $relativeFullPath => $realpath],
                $this->asset->getOptions()->getSymlinks()
            )
        );

        return $this->environment->getBaseUrl() . $relativeFullPath;
    }
}
