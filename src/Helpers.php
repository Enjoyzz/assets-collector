<?php

namespace Enjoys\AssetsCollector;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function Enjoys\FileSystem\createDirectory;

final class Helpers
{

    public static function getHttpScheme(): string
    {
        if (isset($_SERVER['HTTP_SCHEME'])) {
            return $_SERVER['HTTP_SCHEME'];
        }

        if (isset($_SERVER['HTTPS']) && \strtolower($_SERVER['HTTPS']) != 'off') {
            return 'https';
        }

        if (isset($_SERVER['SERVER_PORT']) && 443 == (int)$_SERVER['SERVER_PORT']) {
            return 'https';
        }
        return 'http';
    }


    /**
     * @param string $path
     * @param int $permissions
     * @param LoggerInterface|null $logger
     * @return void
     * @throws \Exception
     */
    public static function createDirectory(string $path, int $permissions = 0775, LoggerInterface $logger = null): void
    {
        $logger ??= new NullLogger();

        if (createDirectory($path, $permissions)) {
            $logger->info(sprintf('Create directory %s', $path));
        }
    }

}
