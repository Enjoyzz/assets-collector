<?php

namespace Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Environment;
use Exception;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * Class Reader
 * @package Enjoys\AssetsCollector\Content
 */
class Reader
{


    private false|string $content;


    private LoggerInterface $logger;

    /**
     * Reader constructor.
     * @param Asset $asset
     * @param Environment $environment
     */
    public function __construct(
        private readonly Asset $asset,
        private readonly Environment $environment,
    ) {
        $this->logger = $this->environment->getLogger();
        $this->content = $this->getContent();
    }

    /**
     * @throws Exception
     */
    public function getContents(): string
    {
        if (!$this->asset->isValid() || $this->content === false) {
            $this->logger->notice(sprintf('Nothing return: path is `%s`', $this->asset->getOrigPath()));
            return '';
        }
        return $this->content;
    }


    private function getContent(): false|string
    {
        if (!$this->asset->isValid()) {
            return false;
        }

        if ($this->asset->isUrl()) {
            return $this->readUrl($this->asset->getPath());
        }
        return $this->readFile($this->asset->getPath());
    }


    private function readUrl(string $url): false|string
    {
        if (
            null !== ($httpClient = $this->environment->getHttpClient())
            && null !== ($requestFactory = $this->environment->getRequestFactory())
        ) {
            return $this->readWithPsrHttpClient($url, $httpClient, $requestFactory);
        }

        try {
            return $this->readWithPhpFileGetContents($url);
        } catch (RuntimeException $e) {
            $this->logger->notice(sprintf("Ошибка чтения содержимого файла: %s", $e->getMessage()));
            return false;
        }
    }

    /**
     * @throws RuntimeException
     */
    private function readWithPhpFileGetContents(string $url): false|string
    {
        //Clear the most recent error
        error_clear_last();
        $content = @file_get_contents($url);
        /** @var null|string[] $error */
        $error = error_get_last();
        if ($error !== null) {
            throw new RuntimeException(sprintf("%s", $error['message']));
        }
        return $content;
    }


    private function readWithPsrHttpClient(
        string $url,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory
    ): false|string {
        try {
            $response = $client->sendRequest(
                $requestFactory->createRequest('get', $url)
            );

            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException(
                    sprintf('HTTP error: %s - %s', $response->getStatusCode(), $response->getReasonPhrase())
                );
            }

            $this->logger->info(sprintf('Read: %s', $url));
            return $response->getBody()->getContents();
        } catch (Throwable $e) {
            $this->logger->notice($e->getMessage());
            return false;
        }
    }

    private function readFile(string $filename): false|string
    {
        if (!file_exists($filename)) {
            $this->logger->notice(sprintf("Файла по указанному пути нет: %s", $filename));
            return false;
        }

        try {
            $content = $this->readWithPhpFileGetContents($filename);
        } catch (RuntimeException $e) {
            $this->logger->notice(sprintf("Ошибка чтения содержимого файла: %s", $e->getMessage()));
            return false;
        }

        $this->logger->info(sprintf('Read: %s', $filename));
        return $content;
    }

    /**
     * @throws Exception
     */
    public function replaceRelativeUrls(): Reader
    {
        if (!$this->asset->isValid() || $this->content === false) {
            return $this;
        }

        if ($this->asset->getOptions()->isReplaceRelativeUrls()) {
            $replaceRelativeUrls = new ReplaceRelative($this->content, $this->asset, $this->environment);
            $this->content = $replaceRelativeUrls->getContent();
        }

        return $this;
    }


    public function minify(): Reader
    {
        if (!$this->asset->isValid() || $this->content === false || !$this->asset->getOptions()->isMinify()) {
            return $this;
        }

        $minifier = $this->environment->getMinifier($this->asset->getType());

        if ($minifier === null) {
            return $this;
        }

        $this->logger->info(sprintf('Minify: %s', $this->asset->getPath()));

        $this->content = $minifier->minify($this->content) . "\n";
        return $this;
    }
}
