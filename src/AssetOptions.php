<?php

namespace Enjoys\AssetsCollector;

final class AssetOptions
{
    /**
     * @var array{'minify': bool, 'reaplaceRelativeUrls': bool, 'notCollect': bool, 'attributes': array|null, 'symlinks': array}
     */
    private array $options = [
        Asset::MINIFY => true,
        Asset::REPLACE_RELATIVE_URLS => true,
        Asset::NOT_COLLECT => false,
        Asset::ATTRIBUTES => null,
        Asset::CREATE_SYMLINK => []
    ];

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
        $this->options[$key] = $value;
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

    /**
     * @return array<string, string|bool|array|null>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function isMinify(): bool
    {
        return $this->options[Asset::MINIFY];
    }

    public function isReplaceRelativeUrls(): bool
    {
        return $this->options[Asset::REPLACE_RELATIVE_URLS];
    }

    public function isNotCollect(): bool
    {
        return $this->options[Asset::NOT_COLLECT];
    }

    public function getAttributes(): ?array
    {
        return $this->options[Asset::ATTRIBUTES];
    }
}
