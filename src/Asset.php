<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use Enjoys\Traits\Options;

class Asset
{
    use Options;

    public const MINIFY = 'minify';
    public const REPLACE_RELATIVE_URLS = 'reaplaceRelativeUrls';
    public const CREATE_SYMLINK = 'symlinks';

    private ?string $id = null;

    /**
     * @var string|false
     */
    private $path;
    private string $type;
    private bool $isUrl;
    private string $origPath;
    private bool $minify;
    private bool $replaceRelativeUrls;
    private string $url = '';


    public function __construct(string $type, string $path, array $params = [])
    {
        $this->setOptions($params);
        $this->type = $type;
        $this->origPath = $path;
        $this->minify = (bool)$this->getOption(self::MINIFY, true);
        $this->replaceRelativeUrls = (bool)$this->getOption(self::REPLACE_RELATIVE_URLS, true);
        $this->isUrl = $this->checkIsUrl($path);
        $this->path = $this->getNormalizedPath($path);
    }

    /**
     * @param string $path
     * @return false|string
     */
    private function getNormalizedPath(string $path)
    {
        if ($this->isUrl()) {
            $this->setId($this->url);
            return $this->url;
        }

        if (false === $projectDir = \getenv('ASSETS_PROJECT_DIRECTORY')) {
            $projectDir = '';
        }
        $paths = [
            $path,
            $projectDir . $path
        ];

        foreach ($paths as $path) {
            if (false !== $normalizedPath = realpath($path)) {
                $this->setId($normalizedPath);
                break;
            }
        }
        return $normalizedPath;
    }

    private function checkIsUrl(string $path): bool
    {
        if (\str_starts_with($path, '//')) {
            $this->url = Helpers::getHttpScheme() . ':' . $path;
            return true;
        }

        if (in_array(strpos($path, '://'), [4, 5])) {
            $this->url = $path;
            return true;
        }

        if (\str_starts_with($path, 'url:') || \str_starts_with($path, 'local:')) {
            $this->url = str_replace(['url:', 'local:'], '', $path);
            return true;
        }

        return false;
    }



    public function isMinify(): bool
    {
        return $this->minify;
    }

    /**
     * @return false|string
     */
    public function getPath()
    {
        return $this->path;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isUrl(): bool
    {
        return $this->isUrl;
    }


    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOrigPath(): string
    {
        return $this->origPath;
    }

    private function setId(string $path): void
    {
        $this->id = md5($path);
    }

    /**
     * @return bool
     */
    public function isReplaceRelativeUrls(): bool
    {
        return $this->replaceRelativeUrls;
    }
}
