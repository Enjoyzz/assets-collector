<?php

namespace Enjoys\AssetsCollector;

final class AssetOptions
{
    private bool $minify = true;
    private bool $reaplaceRelativeUrls = true;
    private bool $notCollect = false;
    private ?array $attributes = null;
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
    public function setOptions(array $options = []): AssetOptions
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
     * @psalm-suppress InvalidPropertyAssignmentValue
     */
    public function setOption(string $key, $value): AssetOptions
    {
        $this->$key = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param string|bool|array|null $defaults
     * @return string|bool|array|null
     */
    public function getOption(string $key, $defaults = null)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        return $defaults;
    }

    public function isMinify(): bool
    {
        return $this->minify;
    }

    public function isReplaceRelativeUrls(): bool
    {
        return $this->reaplaceRelativeUrls;
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
