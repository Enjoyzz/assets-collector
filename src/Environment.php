<?php

namespace Enjoys\AssetsCollector;

use Closure;
use Enjoys\AssetsCollector\Exception\PathDirectoryIsNotValid;
use Enjoys\AssetsCollector\Strategy\ManyFilesStrategy;
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

    private ?string $version = null;

    private string $paramVersion = 'v';

    /**
     * @var class-string<Strategy>|Strategy
     */
    private string|Strategy $strategy = ManyFilesStrategy::class;

    /**
     * @var array<string, Closure(string):string|Minifier|null>
     */
    private array $minifiers = [];

    /**
     * @var array<string, Closure(Asset[]):string|Renderer|null>
     */
    private array $renderers = [];

    private ?ClientInterface $httpClient = null;

    private ?RequestFactoryInterface $requestFactory = null;

    /**
     * Environment constructor.
     * @param string $compileDir
     * @param string $projectDir Если пустая строка, то realpath вернет текущую рабочую директорию
     * @param LoggerInterface $logger
     */
    public function __construct(
        string $compileDir = '/',
        string $projectDir = '',
        private LoggerInterface $logger = new NullLogger()
    ) {
        $projectDir = realpath($projectDir);

        if ($projectDir === false) {
            throw new PathDirectoryIsNotValid(
                "The project directory is not installed or the directory could not be determined automatically"
            );
        }
        putenv("ASSETS_PROJECT_DIRECTORY=$projectDir/");

        $this->projectDir = $projectDir;
        $this->compileDir = $this->normalizeCompileDir($compileDir);
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    /**
     * Функцию realpath() нельзя применять так как директории изначально может не быть,
     * она может потом быть создана, если будут права
     */
    private function normalizeCompileDir(string $path): string
    {
        if (str_starts_with($path, $this->getProjectDir())) {
            $path = str_replace($this->getProjectDir(), '', $path);
        }
        $path = $this->getProjectDir() . '/' . ltrim($path, '/\.');
        return rtrim($path, '/');
    }

    public function getCompileDir(): string
    {
        return $this->compileDir;
    }

    public function setBaseUrl(string $baseUrl = '/'): Environment
    {
        $this->baseUrl = rtrim($baseUrl, '\/');
        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setCacheTime(int $cacheTime): Environment
    {
        $this->cacheTime = $cacheTime;
        return $this;
    }

    public function getCacheTime(): int
    {
        return $this->cacheTime;
    }

    public function getStrategy(): Strategy
    {
        if ($this->strategy instanceof Strategy) {
            return $this->strategy;
        }
        return new $this->strategy();
    }

    /**
     * @param class-string<Strategy>|Strategy $strategy
     */
    public function setStrategy(string|Strategy $strategy): Environment
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
     * @return Environment
     */
    public function setMinifier(AssetType $type, Minifier|Closure|null $minifier): Environment
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
     * @return Environment
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

    public function setParamVersion(string $paramVersion): Environment
    {
        $this->paramVersion = $paramVersion;
        return $this;
    }

    public function setVersion(string $version): Environment
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getVersionQuery(): array
    {
        if ($this->version === null) {
            return [];
        }
        return [$this->paramVersion => $this->version];
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getParamVersion(): string
    {
        return $this->paramVersion;
    }

}
