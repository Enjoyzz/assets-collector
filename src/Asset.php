<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector;

use Enjoys\Traits\Options;

class Asset
{
    use Options;

    public const MINIFY = 'minify';
    public const PARAM_CREATE_SYMLINK = 'symlinks';

    private ?string $id = null;
    /**
     * @var false|string
     */
    private $path;
    private string $type;
    private bool $isUrl;
    private string $origPath;
    private bool $minify;


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
        $this->minify = $this->getOption(self::MINIFY, true);
        $this->isUrl = $this->checkIsUrl();

        $this->normalizePath();
    }

    private function normalizePath(): void
    {
        if ($this->isUrl() && $this->path !== false) {
            $this->setId();
            return;
        }

        if (false === $projectDir = \getenv('ASSETS_PROJECT_DIRECTORY')) {
            $projectDir = '';
        }
        $paths = [
            $this->path,
            $projectDir . $this->path
        ];

        foreach ($paths as $path) {
            if (false !== $this->path = realpath($path)) {
                $this->setId();
                break;
            }
        }
    }

    private function checkIsUrl(): bool
    {
        if (\str_starts_with($this->path, '//')) {
            $this->path = Helpers::getHttpScheme() . ':' . $this->path;
            return true;
        }

        if (in_array(strpos($this->path, '://'), [4, 5])) {
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

    private function setId(): void
    {
        $this->id = md5($this->path);
    }
}
