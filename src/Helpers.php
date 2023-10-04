<?php

namespace Enjoys\AssetsCollector;

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


}
