<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\Environment;
use Enjoys\UrlConverter;
use Exception;
use Psr\Log\LoggerInterface;

use function str_replace;


final class ReplaceRelative
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly string $content,
        private readonly Asset $asset,
        private readonly Environment $environment
    ) {
        $this->logger = $environment->getLogger();
    }


    /**
     * @throws Exception
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

        $this->logger->info(sprintf('ReplaceRelativeUrls: %s', $this->asset->getPath()));
        return $result ?? '';
    }

    /**
     * @throws Exception
     */
    private function getNormalizedPath(string $relativePath): false|string
    {
        if ($this->asset->isUrl()) {
            return $this->replaceUrls($this->asset->getPath(), $relativePath);
        }
        return $this->replacePath($this->asset->getPath(), $relativePath);
    }


    private function replaceUrls(string $baseUrl, string $relativeUrl): false|string
    {
        $urlConverter = new UrlConverter();
        return $urlConverter->relativeToAbsolute($baseUrl, $relativeUrl);
    }

    /**
     * @throws Exception
     * @psalm-suppress PossiblyFalseOperand
     */
    private function replacePath(string $filePath, string $relativePath): false|string
    {
        $realpath = realpath(
            pathinfo($filePath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR
            . (parse_url($relativePath, PHP_URL_PATH) ?? '')
        );

        if ($realpath === false) {
            return false;
        }

        $relativeFullPath = str_replace(
            '\\',
            '/',
            str_replace(
                [
                    $this->environment->getCompileDir(),
                    $this->environment->getProjectDir()
                ],
                '',
                $realpath
            )
        );

        /** @infection-ignore-all */
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
