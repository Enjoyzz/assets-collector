<?php

namespace Tests\Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Content\Reader;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{

    public function testLocalFile(): void
    {
        $reader = new Reader(new Asset('css', __DIR__ . '/../fixtures/test.css'), []);
        $this->assertSame("body{color:#00008b}\n", $reader->getContents());
    }

    public function testLocalFileNoMinify(): void
    {
        $reader = new Reader(new Asset('css', __DIR__ . '/../fixtures/test.css', [Asset::MINIFY => false]), []);
        $this->assertSame(
            <<<CSS
body {
    color: darkblue;
}
CSS,
            $reader->getContents()
        );
    }


    public function testReturnGetContentReadFalse(): void
    {
        $reader = new Reader(new Asset('css', '/'), []);
        $this->assertSame('', $reader->getContents());
    }

    public function testReturnGetContentFileExistsFalse(): void
    {
        $reader = new Reader(new Asset('css', '/test.css'), []);
        $this->assertSame('', $reader->getContents());
    }
}
