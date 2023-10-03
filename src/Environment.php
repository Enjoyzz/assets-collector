<?php

namespace Enjoys\AssetsCollector;

use Closure;
use Enjoys\AssetsCollector\Exception\PathDirectoryIsNotValid;
use Enjoys\AssetsCollector\Exception\UnexpectedParameters;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function putenv;

class Environment
{
    private string $projectDir;
    private string $compileDir;
    private string $baseUrl = '';
    private int $cacheTime = -1;
    private int $strategy = Assets::STRATEGY_MANY_FILES;
    private ?string $version = null;
    private string $paramVersion = 'v';
    private LoggerInterface $logger;
    private ?ClientInterface $httpClient = null;
    private ?RequestFactoryInterface $requestFactory = null;
    private int $directoryPermissions = 0775;


    /**
     * @var array<string, Closure(string):string|Minifier|null>
     */
    private array $minifiers = [];

    /**
     * @var array<string, Closure(Asset[]):string|Renderer|null>
     */
    private array $renderers = [];

    /**
     * Environment constructor.
     * @param string $compileDir
     * @param string $projectDir Если пустая строка, то realpath вернет текущую рабочую директорию
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    public function __construct(string $compileDir = '/', string $projectDir = '')
    {
        $projectDir = realpath($projectDir);

        if ($projectDir === false) {
            throw new PathDirectoryIsNotValid(
                "Не установлена директория проекта или не удалось автоматически определить директорию"
            );
        }
        $this->projectDir = $projectDir;
        putenv("ASSETS_PROJECT_DIRECTORY={$this->projectDir}/");

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
        return $this->paramVersion . '=' . $this->version;
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

    public function setLogger(LoggerInterface $logger): Environment
    {
        $this->logger = $logger;
        return $this;
    }


    /**
     * @return int
     */
    public function getDirectoryPermissions(): int
    {
        return $this->directoryPermissions;
    }

    /**
     * @param int $directoryPermissions
     */
    public function setDirectoryPermissions(int $directoryPermissions): void
    {
        $this->directoryPermissions = $directoryPermissions;
    }


    public function getHttpClient(): ?ClientInterface
    {
        return $this->httpClient;
    }

    public function setHttpClient(?ClientInterface $httpClient): Environment
    {
        $this->httpClient = $httpClient;
        return $this;
    }


    public function getRequestFactory(): ?RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    public function setRequestFactory(?RequestFactoryInterface $requestFactory): Environment
    {
        $this->requestFactory = $requestFactory;
        return $this;
    }

    /**
     * @param AssetType $type
     * @param Minifier|Closure(string):string|null $minifier
     * @return $this
     */
    public function setMinifier(AssetType $type, Minifier|Closure|null $minifier): static
    {
        $this->minifiers[$type->value] = $minifier;
        return $this;
    }

    public function getMinifier(AssetType $type): ?Minifier
    {
        return MinifierFactory::get($this->minifiers[$type->value] ?? null);
    }

    /**
     * @param AssetType $type
     * @param Renderer|Closure(Asset[]):string|null $renderer
     * @return $this
     */
    public function setRenderer(AssetType $type, Renderer|Closure|null $renderer): Environment
    {
        $this->renderers[$type->value] = $renderer;
        return $this;
    }

    public function getRenderer(AssetType $type): Renderer
    {
        $renderer = $this->renderers[$type->value]
            ?? RenderFactory::getDefaultRenderer($type);

        if ($renderer instanceof Renderer) {
            return $renderer;
        }
        return RenderFactory::createFromClosure($renderer);

    }

    public function getVersionQuery(): array
    {
        if ($this->version === null) {
            return [];
        }
        return [$this->paramVersion => $this->version];
    }

}
