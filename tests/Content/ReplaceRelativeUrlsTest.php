<?php

namespace Tests\Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Content\ReplaceRelativeUrls;
use PHPUnit\Framework\TestCase;

class ReplaceRelativeUrlsTest extends TestCase
{

    public function data()
    {
        return [
            ['http://test.com/style.css', 'http://test.com'],
            ['https://test.com/style.css', 'https://test.com'],
            ['https://test.com:8080/style.css', 'https://test.com:8080']
        ];
    }

    /**
     * @dataProvider data
     */
    public function testReplaceRelativeUrls($url, $domain)
    {

        $content = <<<CONTENT
url('/test');
url('yandex.com');
url('//google.com');
url(//google.com);
url(google);
url('http://test.com');
url("data:image")
url('inner/path/style.css');
CONTENT;

        $expectContent = <<<CONTENT
url('{$domain}/test');
url('{$domain}/yandex.com');
url('//google.com');
url(//google.com);
url({$domain}/google);
url('http://test.com');
url("data:image")
url('{$domain}/inner/path/style.css');
CONTENT;

        $processor = new ReplaceRelativeUrls($content, $url);
        $this->assertSame($expectContent, $processor->getContent());
    }
}
