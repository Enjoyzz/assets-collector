<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;


use Enjoys\Traits\Options;

class Asset
{
    use Options;

    public const TYPE_CSS = 'css';
    public const TYPE_JS = 'js';

    public const PARAM_MINIFY = 'minify';
    private ?string $id = null;
    /**
     * @var false|string
     */
    private $path;
    private string $type;
    private bool $isUrl;
    private string $origPath;

    /**
     * Asset constructor.
     * @param string $type
     * @param string $path
     * @param array<mixed> $params
     */
    public function __construct(string $type, string $path, array $params = [])
    {
        $this->setOptions($params);
        $this->type = $type;
        $this->origPath = $path;
        $this->path = $path;
        $this->isUrl = $this->checkIsUrl();

        $this->normalizePath();
    }

    private function normalizePath(): void
    {
        if ($this->isUrl() && $this->path !== false) {
            $this->setId();
            return;
        }

        if (false !== $this->path = realpath($this->path)) {
            $this->setId();
        }
    }

    /**
     * @todo подставить схему сервера
     */
    private function checkIsUrl(): bool
    {
        if (\str_starts_with($this->path, '//')) {
            $this->path = $this->defineHttpScheme() . ':' . $this->path;
            return true;
        }

        if (in_array(strpos($this->path, '://'), [4, 5])) {
            return true;
        }
        return false;
    }

    private function defineHttpScheme()
    {
        $scheme = isset($_SERVER['HTTP_SCHEME']) ? $_SERVER['HTTP_SCHEME'] : (
        (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ||
            (isset($_SERVER['SERVER_PORT']) && 443 == $_SERVER['SERVER_PORT'])
        ) ? 'https' : 'http'
        );
        return $scheme;
    }


    public function isMinify(): bool
    {
        return $this->getOption(self::PARAM_MINIFY, true);
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

    private function setId(): void
    {
        $this->id = md5($this->path);
    }


}