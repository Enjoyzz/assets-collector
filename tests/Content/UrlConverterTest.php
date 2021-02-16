<?php

namespace Tests\Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Content\UrlConverter;
use PHPUnit\Framework\TestCase;

class UrlConverterTest extends TestCase
{
    public function data()
    {
        return [
            ['http://yandex.ru/test.css', '1/2/3/', 'http://yandex.ru/1/2/3/'],
            ['http://yandex.ru/test.css', '../3/', 'http://yandex.ru/3/'],
            ['http://yandex.ru/test.css', '../../../../3/', 'http://yandex.ru/3/'],
            ['http://yandex.ru/1/2/3/test.css', '../4/', 'http://yandex.ru/1/2/4/'],
            ['http://yandex.ru/1/2/3/test.css', './4/', 'http://yandex.ru/1/2/3/4/'],
            ['http://yandex.ru/1/2/3/test.css', '../../4/', 'http://yandex.ru/1/4/'],
            ['http://yandex.ru/1/2/3/test.css', '/4', 'http://yandex.ru/4'],
        ];
    }

    /**
     * @dataProvider data
     */
    public function test($baseUrl, $relativeUrl, $expect)
    {
        $this->assertSame($expect, (new UrlConverter())->relativeToAbsolute($baseUrl, $relativeUrl));
    }

}

