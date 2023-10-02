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
    private string $paramVersion = '?v=';
    private LoggerInterface $logger;
    private ?ClientInterface $httpClient = null;
    private ?RequestFactoryInterface $requestFactory = null;
    private int $directoryPermissions = 0775;


    private Closure|RenderInterface|null $renderCss = null;

    private Closure|RenderInterface|null $renderJs = null;

    /**
     * @var array<string, Closure|Minifier|null>
     */
    private array $minifiers = [];

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

    public function setMinifier(AssetType $type, Minifier|Closure|null $minifier): static
    {
        $this->minifiers[$type->value] = $minifier;
        return $this;
    }

    public function getMinifier(AssetType $type): ?Minifier
    {
        $minifier = $this->minifiers[$type->value] ?? null;
        if ($minifier === null) {
            return null;
        }

        if ($minifier instanceof Minifier) {
            return $minifier;
        }

        return new class($minifier) implements Minifier {

            /**
             * @param Closure(string): string $minifier
             */
            public function __construct(private readonly \Closure $minifier)
            {
            }

            public function minify(string $content): string
            {
                return call_user_func($this->minifier, $content);
            }
        };
    }


    /**
     * @param string|AssetType $type
     * @return Minifier|null
     */
    public function getMinify(string|AssetType $type): Minifier|null
    {
        $type = AssetType::normalize($type);

        $minify = match ($type->value) {
            'css' => $this->cssMinify,
            'js' => $this->jsMinify,
            default => null
        };

        if ($minify === null) {
            return null;
        }

        if ($minify instanceof Minifier) {
            return $minify;
        }

        return new class($minify) implements Minifier {

            /**
             * @param Closure(string): string $minify
             */
            public function __construct(private readonly \Closure $minify)
            {
            }

            public function minify(string $content): string
            {
                return call_user_func($this->minify, $content);
            }
        };
    }

    public function getRenderer(AssetType|string $type): RenderInterface
    {
        $type = AssetType::normalize($type);

        $renderer = match ($type->value) {
            'css' => $this->getRenderCss(),
            'js' => $this->getRenderJs(),
            default => throw new UnexpectedParameters('Possible use only css or js')
        };

        if ($renderer instanceof RenderInterface) {
            return $renderer;
        }

        return new class($renderer) implements RenderInterface {

            /**
             * @param Closure(array): string $renderer
             */
            public function __construct(private \Closure $renderer)
            {
            }

            public function getResult(array $paths): string
            {
                return call_user_func($this->renderer, $paths);
            }
        };
    }

    private function getRenderCss(): RenderInterface|\Closure
    {
        return $this->renderCss ?? function (array $paths): string {
            $result = '';
            /** @var array<string, string|null>|null $attributes */
            foreach ($paths as $path => $attributes) {
                $attributes = array_merge(['type' => 'text/css', 'rel' => 'stylesheet'], (array)$attributes);
                $result .= sprintf(
                    "<link%s href='{$path}{$this->getVersion()}'>\n",
                    (new Attributes($attributes))->__toString()
                );
            }
            return $result;
        };
    }

    private function getRenderJs(): RenderInterface|\Closure
    {
        return $this->renderJs ?? function (array $paths): string {
            $result = '';
            /** @var array<string, string|null>|null $attributes */
            foreach ($paths as $path => $attributes) {
                $result .= sprintf(
                    "<script%s src='{$path}{$this->getVersion()}'></script>\n",
                    (new Attributes($attributes))->__toString()
                );
            }
            return $result;
        };
    }

    public function setRenderCss(Closure|RenderInterface|null $renderCss): Environment
    {
        $this->renderCss = $renderCss;
        return $this;
    }

    public function setRenderJs(Closure|RenderInterface|null $renderJs): Environment
    {
        $this->renderJs = $renderJs;
        return $this;
    }
}
