<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use function getenv;
use function str_starts_with;

class Asset
{

    private ?string $id = null;

    private string|false $path;
    private bool $isUrl;
    private string $origPath;
    private string $url = '';

    private AssetOption $options;

    private AssetType $type;

    /**
     * @param AssetType|string $type
     * @param string $path
     * @param array<string, string|bool|array|null> $options
     */
    public function __construct(AssetType|string $type, string $path, array $options = [])
    {
        $this->type = AssetType::normalize($type);

        $this->origPath = $path;
        $this->options = new AssetOption($options);

        $this->isUrl = $this->checkIsUrl($path);
        $this->path = $this->getNormalizedPath($path);

        $this->setId($this->path);
    }

    private function getNormalizedPath(string $path): false|string
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

    public function getPath(): false|string
    {
        return $this->path;
    }

    public function getType(): AssetType
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

    private function setId(false|string $path): void
    {
        if ($path === false) {
            return;
        }
        $this->id = md5($path . serialize($this->getOptions()));
    }

    public function getOptions(): AssetOption
    {
        return $this->options;
    }
}
