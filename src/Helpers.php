<?php

namespace Enjoys\AssetsCollector;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function Enjoys\FileSystem\createDirectory;
use function Enjoys\FileSystem\CreateSymlink;
use function Enjoys\FileSystem\makeSymlink;
use function Enjoys\FileSystem\writeFile;

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
     * @param string $file
     * @param string $data
     * @param string $mode
     * @param LoggerInterface|null $logger
     * @return void
     * @throws \Exception
     */
    public static function writeFile(
        string $file,
        string $data,
        string $mode = 'w',
        LoggerInterface $logger = null
    ): void {
        $logger ??= new NullLogger();
        writeFile($file, $data, $mode);
        $logger->info(sprintf('Write to: %s', $file));
    }

    public static function createEmptyFile(string $file, LoggerInterface $logger = null): void
    {
        $logger ??= new NullLogger();
        writeFile($file, '');
        $logger->info(sprintf('Create file: %s', $file));
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

    /**
     * @param string $link
     * @param string $target
     * @param LoggerInterface|null $logger
     * @throws \Exception
     */
    public static function createSymlink(string $link, string $target, LoggerInterface $logger = null): void
    {
        $logger ??= new NullLogger();

        try {
            if (makeSymlink($link, $target)) {
                $logger->info(sprintf('Created symlink: %s', $link));
            }
        } catch (\Exception $e) {
            $logger->notice($e->getMessage());
        }
    }
}
