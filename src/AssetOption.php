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
    /**
     * @var array<string, string>
     */
    private array $symlinks = [];


    /**
     * @param array<string, bool|array> $options
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->setOption($key, $value);
        }
    }

    public function setOption(string $key, bool|array $value): AssetOption
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

    /**
     * @return array<string, string>
     */
    public function getSymlinks(): array
    {
        return $this->symlinks;
    }
}
