<?php

namespace Enjoys\AssetsCollector;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Helpers
{

    public static function getHttpScheme(): string
    {
        $scheme = 'http';
        if (isset($_SERVER['HTTP_SCHEME'])) {
            return $_SERVER['HTTP_SCHEME'];
        }

        if (isset($_SERVER['HTTPS']) && \strtolower($_SERVER['HTTPS']) != 'off') {
            return 'https';
        }

        if (isset($_SERVER['SERVER_PORT']) && 443 == $_SERVER['SERVER_PORT']) {
            return 'https';
        }
        return $scheme;
    }

    /**
     * @param string $file
     * @param string $data
     * @param string $mode
     * @param LoggerInterface|null $logger
     * @return void
     */
    public static function writeFile(
        string $file,
        string $data,
        string $mode = 'w',
        LoggerInterface $logger = null
    ): void {
        $logger ??= new NullLogger();

        $f = fopen($file, $mode);
        if ($f !== false) {
            fwrite($f, $data);
            fclose($f);
            $logger->info(sprintf('Write to: %s', $file));
        }
    }

    public static function createEmptyFile(string $file, LoggerInterface $logger = null): void
    {
        $logger ??= new NullLogger();

        $f = fopen($file, 'w');
        if ($f !== false) {
            fwrite($f, '');
            fclose($f);
            $logger->info(sprintf('Create file: %s', $file));
        }
    }

    /**
     * @param string $path
     * @param int $permissions
     * @param LoggerInterface|null $logger
     * @return void
     * @throws \Exception
     */
    public static function createDirectory(string $path, int $permissions = 0777, LoggerInterface $logger = null): void
    {
        $logger ??= new NullLogger();

        if (preg_match("/(\/\.+|\.+)$/i", $path)) {
            throw new \Exception(
                sprintf("Нельзя создать директорию: %s", $path)
            );
        }

        //Clear the most recent error
        error_clear_last();

        if (!is_dir($path)) {
            if (@mkdir($path, $permissions, true) === false) {
                /** @var string[] $error */
                $error = error_get_last();
                throw new \Exception(
                    sprintf("Не удалось создать директорию: %s! Причина: %s", $path, $error['message'])
                );
            }
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

        $directory = pathinfo($link, PATHINFO_DIRNAME);
        Helpers::createDirectory($directory, 0755, $logger);

        if (file_exists($link)) {
            symlink($target, $link);
            $logger->info(sprintf('Created symlink: %s', $link));
        }
    }
}
