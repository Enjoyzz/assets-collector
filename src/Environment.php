<?php

namespace Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Content\Minify\Adapters\NullMinify;
use Enjoys\AssetsCollector\Content\Minify\MinifyInterface;
use Enjoys\AssetsCollector\Exception\PathDirectoryIsNotValid;
use Enjoys\AssetsCollector\Exception\UnexpectedParameters;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Environment
{
    private string $projectDir;
    private string $compileDir;
    private string $baseUrl = '';
    private int $cacheTime = -1;
    private int $strategy = Assets::STRATEGY_MANY_FILES;
    private string $render = Assets::RENDER_HTML;
    private ?string $version = null;
    private string $paramVersion = '?v=';
    private LoggerInterface $logger;
    /**
     * @var array{css: MinifyInterface, js: MinifyInterface}
     */
    private array $minify;

    /**
     * Environment constructor.
     * @param string $compileDir
     * @param string $projectDir Если пустая строка, то realpath вернет текущую рабочую директорию
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    public function __construct(string $compileDir = '/', string $projectDir = '')
    {
        $this->minify = [
            'css' => new NullMinify(),
            'js' => new NullMinify()
        ];

        $projectDir = realpath($projectDir);

        if ($projectDir === false) {
            throw new PathDirectoryIsNotValid(
                "Не установлена директория проекта или не удалось автоматически определить директорию"
            );
        }
        $this->projectDir = $projectDir;
        \putenv("ASSETS_PROJECT_DIRECTORY={$this->projectDir}/");

        $this->compileDir = $this->setCompileDir($compileDir);
        $this->logger = new NullLogger();
    }

    /**
     * @return string
     */
    public function getProjectDir(): string
    {
        return $this->projectDir;
    }


    /**
     * Функцию realpath() нельзя применять так как директории изначально может не быть,
     * она может потом быть создана, если будут права
     * @param string $path
     * @return string
     */
    private function setCompileDir(string $path): string
    {
        if (str_starts_with($path, $this->getProjectDir())) {
            $path = str_replace($this->getProjectDir(), '', $path);
        }
        $path = $this->getProjectDir() . '/' . ltrim($path, '/\.');
        return rtrim($path, '/');
    }

    /**
     * @return string
     */
    public function getCompileDir(): string
    {
        return $this->compileDir;
    }


    /**
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl(string $baseUrl = '/'): Environment
    {
        $this->baseUrl = rtrim($baseUrl, '\/');
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version): Environment
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): ?string
    {
        if ($this->version === null) {
            return null;
        }
        return $this->paramVersion . $this->version;
    }

    /**
     * @param string $paramVersion
     * @return $this
     */
    public function setParamVersion(string $paramVersion): Environment
    {
        $this->paramVersion = $paramVersion;
        return $this;
    }


    /**
     * @param int $cacheTime
     * @return $this
     */
    public function setCacheTime(int $cacheTime): Environment
    {
        $this->cacheTime = $cacheTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getCacheTime(): int
    {
        return $this->cacheTime;
    }

    public function getStrategy(): int
    {
        return $this->strategy;
    }

    public function getRender(): string
    {
        return $this->render;
    }

    /**
     * @param int $strategy
     * @return Environment
     */
    public function setStrategy(int $strategy): Environment
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setMinifyJS(MinifyInterface $minifyImpl): void
    {
        $this->minify['js'] = $minifyImpl;
    }

    public function setMinifyCSS(MinifyInterface $minifyImpl): void
    {
        $this->minify['css'] = $minifyImpl;
    }

    /**
     * @param string $type
     * @return MinifyInterface
     */
    public function getMinify(string $type): MinifyInterface
    {
        if (!array_key_exists($type, $this->minify)) {
            throw new UnexpectedParameters('Possible use only css or js');
        }
        return $this->minify[$type];
    }
}
