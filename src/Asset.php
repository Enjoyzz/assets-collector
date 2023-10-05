<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use GuzzleHttp\Psr7\Uri;

use function getenv;
use function str_starts_with;

class Asset
{

    private string $id = '';
    private string $path = '';
    private bool $valid = false;

    private bool $isUrl;
    private string $origPath;
    private string $url = '';

    private AssetOption $options;
    private AttributeCollection $attributeCollection;


    /**
     * @param AssetType $type
     * @param string $path
     * @param array<string, bool|array> $options
     */
    public function __construct(private readonly AssetType $type, string $path, array $options = [])
    {
        $this->origPath = $path;

        /**  @psalm-suppress MixedArgumentTypeCoercion  */
        $this->attributeCollection = new AttributeCollection($options[AssetOption::ATTRIBUTES] ?? []);

        $this->options = new AssetOption($options);

        $this->isUrl = $this->checkIsUrl($path);
        $path = $this->getNormalizedPath($path);

        if ($path !== false){
            $this->valid = true;
            $this->path = $path;
            $this->setId($this->path);
        }

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

    public function getPath(): string
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

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrigPath(): string
    {
        return $this->origPath;
    }

    private function setId(string $path): void
    {
        $this->id = md5($path . serialize($this->getOptions()));
    }

    public function getOptions(): AssetOption
    {
        return $this->options;
    }

    public function getAttributeCollection(): AttributeCollection
    {
        return $this->attributeCollection;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

}
