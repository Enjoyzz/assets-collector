<?php

declare(strict_types=1);


namespace Enjoys\AssetsCollector\Content;


use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Helpers;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

final class ReplaceRelativePaths
{
    use LoggerAwareTrait;

    private string $content;

    private string $domain;
    private string $path;
    private Environment $environment;

    public function __construct(string $content, string $path, Environment $environment)
    {
        $this->environment = $environment;
        $this->content = $content;
        $this->logger = new NullLogger();
        $this->path = pathinfo($path, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getContent(): string
    {
        $result = preg_replace_callback(
            '/(url\([\'"]?)(?!["\'a-z]+:|[\'"]?\/{2})(.+[^\'"])([\'"]?\))/i',
            function ($m) {
                $realpath = realpath($this->path . $m[2]);

                if ($realpath === false) {
                    return $m[1] . $m[2] . $m[3];
                }
                $relativeFullPath = \str_replace(
                    '\\',
                    '/',
                    \str_replace(
                        [
                            $this->environment->getCompileDir(),
                            $this->environment->getProjectDir()
                        ]
                        ,
                        '',
                        $realpath
                    )
                );

                Helpers::createSymlink($this->environment->getCompileDir() . $relativeFullPath, $realpath, $this->logger);

                return $m[1] . $this->environment->getBaseUrl() . $relativeFullPath . $m[3];
            },
            $this->content
        );

        if ($result === null) {
            $this->logger->notice(sprintf('Regex return null value. Returned empty string: %s', $this->path));
            return '';
        }
        $this->logger->info(sprintf('ReplaceRelativePaths: %s', $this->path));
        return $result;
    }
}