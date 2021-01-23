<?php


namespace Enjoys\AssetsCollector;


class Helpers
{

    static public function getHttpScheme(): string
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
}