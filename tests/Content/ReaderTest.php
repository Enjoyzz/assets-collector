<?php

namespace Tests\Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Content\Reader;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{

    private ?Environment $environment;


    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $this->environment = new Environment('_compile', __DIR__ . '/../');
    }

    protected function tearDown(): void
    {
        $this->environment = null;
    }

    public function testLocalFile(): void
    {
        $reader = new Reader(new Asset('css', __DIR__ . '/../fixtures/test.css'), [], $this->environment);
        $this->assertSame("body{color:#00008b}\n", $reader->getContents());
    }

    public function testLocalFileNoMinify(): void
    {
        $reader = new Reader(
            new Asset('css', __DIR__ . '/../fixtures/test.css', [Asset::MINIFY => false]),
            [],
            $this->environment
        );
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
        $reader = new Reader(new Asset('css', '/'), [], $this->environment);
        $this->assertSame('', $reader->getContents());
    }

    public function testReturnGetContentFileExistsFalse(): void
    {
        $reader = new Reader(new Asset('css', '/test.css'), [], $this->environment);
        $this->assertSame('', $reader->getContents());
    }

    public function testWithReplaceRelativePath(): void
    {
        $reader = new Reader(
            new Asset('css', __DIR__ . '/../fixtures/sub/css/style.css', [Asset::MINIFY => false]),
            [],
            $this->environment
        );
        $this->assertSame(
            <<<CSS
@font-face {
    src:url('/fixtures/sub/fonts/font.eot') format('eot');
    src:url('/fixtures/sub/css/font2.eot');
    src:url('/fixtures/sub/css/font2.eot');
    src:url('/font3.eot');
}
CSS
            ,
            $reader->getContents()
        );
    }
}
