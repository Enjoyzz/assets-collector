<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content;

use Enjoys\UrlConverter;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class ReplaceRelativeUrls
 * @package Enjoys\AssetsCollector\Content
 */
class ReplaceRelativeUrls
{
    private string $content;

    private string $domain;

    private string $url;
    private LoggerInterface $logger;

    public function __construct(string $content, string $url)
    {
        $this->content = $content;
        $this->logger = new NullLogger();
        $this->url = $url;
    }


    /**
     * @return string
     */
    public function getContent(): string
    {
        $result = preg_replace_callback('/(url\([\'"]?)(?!["\'a-z]+:|[\'"]?\/{2})(.+[^\'"])([\'"]?\))/i', function ($m){
            $urlConverter = new UrlConverter();
            return $m[1] . $urlConverter->relativeToAbsolute($this->url, $m[2]) . $m[3];
        }, $this->content);

        if ($result === null) {
            $this->logger->notice(sprintf('Regex return null value. Returned empty string: %s', $this->url));
            return '';
        }
        $this->logger->info(sprintf('ReplaceRelativeUrls: %s', $this->url));
        return $result;
    }

    /**
     * @param LoggerInterface|NullLogger $logger
     */
    public function setLogger($logger): void
    {
        $this->logger = $logger;
    }


}
