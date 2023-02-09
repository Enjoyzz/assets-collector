<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use function getenv;
use function str_starts_with;

class Asset
{

    public const MINIFY = 'minify';
    public const REPLACE_RELATIVE_URLS = 'reaplaceRelativeUrls';
    public const CREATE_SYMLINK = 'symlinks';
    public const NOT_COLLECT = 'notCollect';
    public const ATTRIBUTES = 'attributes';

    private ?string $id = null;

    /**
     * @var string|false
     */
    private $path;
    private string $type;
    private bool $isUrl;
    private string $origPath;
    private string $url = '';

    private AssetOptions $options;


    /**
     * @param string $type
     * @param string $path
     * @param array<string, string|bool|array|null> $options
     */
    public function __construct(string $type, string $path, array $options = [])
    {
        $this->type = $type;
        $this->origPath = $path;
        $this->options =  new AssetOptions($options);

        $this->isUrl = $this->checkIsUrl($path);
        $this->path = $this->getNormalizedPath($path);

        $this->setId($this->path);
    }

    /**
     * @param string $path
     * @return false|string
     */
    private function getNormalizedPath(string $path)
    {
        if ($this->isUrl()) {
            return $this->url;
        }

        if (false === $projectDir = getenv('ASSETS_PROJECT_DIRECTORY')) {
            $projectDir = '';
        }
        $paths = [
            $path,
            $projectDir . $path
        ];

        foreach ($paths as $path) {
            if (false !== $normalizedPath = realpath($path)) {
                return $normalizedPath;
            }
        }
        return false;
    }

    private function checkIsUrl(string $path): bool
    {
        if (str_starts_with($path, '//')) {
            $this->url = Helpers::getHttpScheme() . ':' . $path;
            return true;
        }

        if (in_array(strpos($path, '://'), [4, 5])) {
            $this->url = $path;
            return true;
        }

        if (str_starts_with($path, 'url:') || str_starts_with($path, 'local:')) {
            $this->url = str_replace(['url:', 'local:'], '', $path);
            return true;
        }

        return false;
    }


    public function isMinify(): bool
    {
        return $this->options->isMinify();
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

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getOrigPath(): string
    {
        return $this->origPath;
    }

    /**
     * @param string|false $path
     */
    private function setId($path): void
    {
        if ($path === false) {
            return;
        }
        $this->id = md5($path . serialize($this->getOptions()->getOptions()));
    }

    public function isReplaceRelativeUrls(): bool
    {
        return $this->options->isReplaceRelativeUrls();
    }

    public function isNotCollect(): bool
    {
        return $this->options->isNotCollect();
    }

    public function getAttributes(): ?array
    {
        return $this->options->getAttributes();
    }

    public function getOptions(): AssetOptions
    {
        return $this->options;
    }
}
