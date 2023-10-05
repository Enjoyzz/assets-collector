<?php

namespace Enjoys\AssetsCollector;

use GuzzleHttp\Psr7\Uri;

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


    public static function addVersionToPath(string $path, array $versionQuery): string
    {
        $url = new Uri($path);
        parse_str($url->getQuery(), $query);
        return $url->withQuery(
            http_build_query(array_merge($query, $versionQuery))
        )->__toString();
    }


}
