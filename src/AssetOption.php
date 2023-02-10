<?php

namespace Enjoys\AssetsCollector;

final class AssetOption
{

    public const MINIFY = 'minify';
    public const REPLACE_RELATIVE_URLS = 'replaceRelativeUrls';
    public const SYMLINKS = 'symlinks';
    public const NOT_COLLECT = 'notCollect';
    public const ATTRIBUTES = 'attributes';

    private bool $minify = true;
    private bool $replaceRelativeUrls = true;
    private bool $notCollect = false;
    private ?array $attributes = null;
    /**
     * @var array<string, string>
     */
    private array $symlinks = [];

    /**
     * @param array<string, string|bool|array|null> $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @param array<string, string|bool|array|null> $options
     * @return $this
     */
    public function setOptions(array $options = []): AssetOption
    {
        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->setOption($key, $value);
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string|bool|array|null $value
     * @return $this
     */
    public function setOption(string $key, $value): AssetOption
    {
        $this->$key = $value;
        return $this;
    }

    public function isMinify(): bool
    {
        return $this->minify;
    }

    public function isReplaceRelativeUrls(): bool
    {
        return $this->replaceRelativeUrls;
    }

    public function isNotCollect(): bool
    {
        return $this->notCollect;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    /**
     * @return array<string, string>
     */
    public function getSymlinks(): array
    {
        return $this->symlinks;
    }
}
