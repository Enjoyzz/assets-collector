<?php

namespace Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Content\Minify\MinifyFactory;
use Enjoys\AssetsCollector\Environment;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Reader
 * @package Enjoys\AssetsCollector\Content
 */
class Reader
{
    /**
     * @var Asset
     */
    private Asset $asset;

    /**
     * @var false|string
     */
    private $content;
    /**
     * @var false|string
     */
    private $path;

    private Environment $environment;
    /**
     * @var LoggerInterface|NullLogger
     */
    private LoggerInterface $logger;

    /**
     * Reader constructor.
     * @param Asset $asset
     * @param Environment $environment
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Asset $asset,
        Environment $environment,
        LoggerInterface $logger = null
    ) {
        $this->environment = $environment;
        $this->asset = $asset;
        $this->logger = $logger ?? $this->environment->getLogger();
        $this->path = $this->asset->getPath();
        $this->content = $this->getContent();
    }

    /**
     * @throws \Exception
     */
    public function getContents(): string
    {
        if ($this->content === false || $this->path === false) {
            $this->logger->notice(sprintf('Nothing return: path is `%s`', $this->asset->getOrigPath()));
            return '';
        }
        return $this->content;
    }


    /**
     * @return false|string
     */
    private function getContent()
    {
        if ($this->path === false) {
            return false;
        }

        if ($this->asset->isUrl()) {
            return $this->readUrl($this->path);
        }
        return $this->readFile($this->path);
    }

    /**
     * @return false|string
     */
    private function readUrl(string $url)
    {
        if (
            null !== ($httpClient = $this->environment->getHttpClient())
            && null !== ($requestFactory = $this->environment->getRequestFactory())
        ) {
            return $this->readWithPsrHttpClient($url, $httpClient, $requestFactory);
        }

        return $this->readWithPhpFileGetContents($url);
    }

    /**
     * @return false|string
     * @throws \RuntimeException
     */
    private function readWithPhpFileGetContents(string $url)
    {
        try {
            //Clear the most recent error
            error_clear_last();
            $content = @file_get_contents($url);
            /** @var null|string[] $error */
            $error = error_get_last();
            if ($error !== null) {
                throw new \RuntimeException(sprintf("%s", $error['message']));
            }
            return $content;
        } catch (\Throwable $e) {
            $this->logger->notice($e->getMessage());
            return false;
        }
    }

    /**
     * @return false|string
     */
    private function readWithPsrHttpClient(
        string $url,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory
    ) {
        try {
            $response = $client->sendRequest(
                $requestFactory->createRequest('get', $url)
            );

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    sprintf('HTTP error: %s - %s', $response->getStatusCode(), $response->getReasonPhrase())
                );
            }

            $this->logger->info(sprintf('Read: %s', $url));
            return $response->getBody()->getContents();
        } catch (\Throwable $e) {
            $this->logger->notice($e->getMessage());
            return false;
        }
    }

    /**
     * @param string $filename
     * @return false|string
     */
    private function readFile(string $filename)
    {
        if (!file_exists($filename)) {
            $this->logger->notice(sprintf("Файла по указанному пути нет: %s", $filename));
            return false;
        }

        try {
            $content = $this->readWithPhpFileGetContents($filename);
        } catch (\RuntimeException $e) {
            $this->logger->notice(sprintf("Ошибка чтения содержимого файла: %s", $e->getMessage()));
            return false;
        }

        $this->logger->info(sprintf('Read: %s', $filename));
        return $content;
    }

    /**
     * @param LoggerInterface|NullLogger $logger
     */
    public function setLogger($logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function replaceRelativeUrlsAndCreatedSymlinks(): Reader
    {
        if ($this->asset->getOptions()->isReplaceRelativeUrls()) {
            $replaceRelativeUrls = new ReplaceRelative($this->content, $this->path, $this->asset, $this->environment);
            $replaceRelativeUrls->setLogger($this->logger);
            $this->content = $replaceRelativeUrls->getContent();
        }

        return $this;
    }

    /**
     * @return void
     */
    public function minify(): Reader
    {
        if ($this->asset->getOptions()->isMinify()) {
            $this->content = MinifyFactory::minify(
                    $this->content,
                    $this->asset->getType(),
                    $this->environment
                )->getContent() . "\n";
            $this->logger->info(sprintf('Minify: %s', $this->path));
        }

        return $this;
    }
}
