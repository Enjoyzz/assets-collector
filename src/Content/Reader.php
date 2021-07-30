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
     * @var false|string
     */
    private $path;

    /**
     * @var array{css: array, js: array}
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
     * @param array{css: array, js: array} $minifyOptions
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
        $this->logger = $logger ?? new NullLogger();
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

        $replaceRelativeUrls = new ReplaceRelative($this->content, $this->path, $this->asset, $this->environment);
        $replaceRelativeUrls->setLogger($this->logger);
        $this->content = $replaceRelativeUrls->getContent();


        if ($this->asset->isMinify()) {
            $this->content = MinifyFactory::minify(
                $this->content,
                $this->asset->getType(),
                $this->environment
            )->getContent() . "\n";
            $this->logger->info(sprintf('Minify: %s', $this->path));
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
