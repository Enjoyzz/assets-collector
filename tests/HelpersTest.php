<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Helpers;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{

    public function testAddVersionToPath()
    {
        $this->assertSame(
            '/d/d.ext?version=test',
            Helpers::addVersionToPath('/d/d.ext', ['version' => 'test'])
        );

        $this->assertSame(
            '//yandex/file?foo=bar&version=1.0',
            Helpers::addVersionToPath('//yandex/file?foo=bar', ['version' => '1.0'])
        );

        $this->assertSame(
            'https://yandex/file?foo=bar&version=2.0',
            Helpers::addVersionToPath('https://yandex/file?foo=bar&version=1.0', ['version' => '2.0'])
        );
    }

    public function testGetHttpScheme()
    {
        $this->assertSame(
            'http',
            Helpers::getHttpScheme()
        );

        $_SERVER['HTTP_SCHEME'] = 'https';
        $this->assertSame(
            'https',
            Helpers::getHttpScheme()
        );
        unset($_SERVER['HTTP_SCHEME']);

        $_SERVER['HTTPS'] = 'on';
        $this->assertSame(
            'https',
            Helpers::getHttpScheme()
        );
        unset($_SERVER['HTTPS']);

        $_SERVER['HTTPS'] = true;
        $this->assertSame(
            'https',
            Helpers::getHttpScheme()
        );
        unset($_SERVER['HTTPS']);

        $_SERVER['HTTPS'] = 'Off';
        $this->assertSame(
            'http',
            Helpers::getHttpScheme()
        );
        unset($_SERVER['HTTPS']);

        $_SERVER['SERVER_PORT'] = '443';
        $this->assertSame(
            'https',
            Helpers::getHttpScheme()
        );
        unset($_SERVER['SERVER_PORT']);

        $_SERVER['SERVER_PORT'] = 443;
        $this->assertSame(
            'https',
            Helpers::getHttpScheme()
        );
        unset($_SERVER['SERVER_PORT']);

        $_SERVER['SERVER_PORT'] = 80;
        $this->assertSame(
            'http',
            Helpers::getHttpScheme()
        );
        unset($_SERVER['SERVER_PORT']);
    }
}
