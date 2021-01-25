<?php

namespace Enjoys\AssetsCollector;

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
     * @return void
     */
    public static function writeFile(string $file, string $data, string $mode = 'w'): void
    {
        $f = fopen($file, $mode);
        if ($f !== false) {
            fwrite($f, $data);
            fclose($f);
        }
    }

    /**
     * @param string $path
     * @param int $permissions
     * @return void
     * @throws \Exception
     */
    public static  function createDirectory(string $path, int $permissions = 0777): void
    {
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
        }
    }
}
