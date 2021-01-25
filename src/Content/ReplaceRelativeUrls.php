<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Class ReplaceRelativeUrls
 * @package Enjoys\AssetsCollector\Content
 */
class ReplaceRelativeUrls
{
    use LoggerAwareTrait;

    private string $content;

    private string $domain;
    private string $url;

    public function __construct(string $content, string $url)
    {
        $this->content = $content;
        $this->domain = $this->getDomain($url);
        $this->url = $url;
        $this->logger = new NullLogger();
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        $result = preg_replace('/(url\([\'"]?)(?!["\'a-z]+:|[\'"]?\/{2})/i', '\1' . $this->domain, $this->content);

        if ($result === null) {
            $this->logger->notice(sprintf('Regex return null value. Returned empty string: %s', $this->url));
            return '';
        }
        $this->logger->info(sprintf('ReplaceRelativeUrls: %s', $this->url));
        return $result;
    }

    private function getDomain(string $path): string
    {
        $domain = '';

        /** @var string[] $url */
        $url = parse_url($path);

        if (isset($url['scheme'])) {
            $domain .= $url['scheme'] . '://';
        }

        if (isset($url['host'])) {
            $domain .= $url['host'];
        }

        if (isset($url['port'])) {
            $domain .= ':' . $url['port'];
        }

        return $domain;
    }
}
