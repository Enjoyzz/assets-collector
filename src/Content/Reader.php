<?php

namespace Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Content\Minify\MinifyFactory;
use Enjoys\AssetsCollector\Environment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
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
     * @var array{css: array<mixed>, js: array<mixed>}
     */
    private array $minifyOptions;
    private Environment $environment;
    /**
     * @var LoggerInterface|NullLogger
     */
    private LoggerInterface $logger;

    /**
     * Reader constructor.
     * @param Asset $asset
     * @param array{css: array<mixed>, js: array<mixed>} $minifyOptions
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Asset $asset,
        array $minifyOptions,
        Environment $environment,
        LoggerInterface $logger = null
    ) {
        $this->environment = $environment;
        $this->asset = $asset;
        $this->logger = $logger ?? new NullLogger();

        $this->content = $this->getContent();
        $this->minifyOptions = $minifyOptions;
    }

    public function getContents(): string
    {
        $path = $this->asset->getPath();

        if ($this->content === false || $path === false) {
            $this->logger->notice(sprintf('Nothing return: path is `%s`', (string)$path));
            return '';
        }

        if ($this->asset->isUrl()) {
            $replaceRelativeUrls = new ReplaceRelativeUrls($this->content, $path);
            $replaceRelativeUrls->setLogger($this->logger);
            $this->content = $replaceRelativeUrls->getContent();
        } else {
            $replaceRelativePath = new ReplaceRelativePaths(
                $this->content,
                $path,
                $this->environment
            );
            $replaceRelativePath->setLogger($this->logger);
            $this->content = $replaceRelativePath->getContent();
        }

        if ($this->asset->isMinify()) {
            $this->content = MinifyFactory::minify(
                $this->content,
                $this->asset->getType(),
                $this->minifyOptions
            )->getContent() . "\n";
            $this->logger->info(sprintf('Minify: %s', $path));
        }
        return $this->content;
    }


    /**
     * @return false|string
     */
    private function getContent()
    {
        if (false !== $path = $this->asset->getPath()) {
            if ($this->asset->isUrl()) {
                return $this->readUrl($path);
            }
            return $this->readFile($path);
        }
        return false;
    }

    /**
     * @param string $url
     * @return false|string
     */
    private function readUrl(string $url)
    {
        try {
            $client = new Client(
                [
                    'verify' => false,
                    'allow_redirects' => true,
                    'headers' => [
                        'User-Agent' =>
                            'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
                    ]
                ]
            );
            $response = $client->get($url);
            $this->logger->info(sprintf('Read: %s', $url));
            return $response->getBody()->getContents();
        } catch (ClientException | GuzzleException $e) {
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
        //Clear the most recent error
        error_clear_last();

        if (!file_exists($filename)) {
            $this->logger->notice(sprintf("Файла по указанному пути нет: %s", $filename));
            return false;
        }
        $content = @file_get_contents($filename);

        /** @var null|string[] $error */
        $error = error_get_last();
        if ($error !== null) {
            $this->logger->notice(sprintf("Ошибка чтения содержимого файла: %s", $error['message']));
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
}
