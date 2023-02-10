<?php

namespace Tests\Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\Content\Minify\Adapters\CssMinify;
use Enjoys\AssetsCollector\Content\Minify\Adapters\JsMinify;
use Enjoys\AssetsCollector\Content\Reader;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;
use Tests\Enjoys\AssetsCollector\HelpersTestTrait;

class ReaderTest extends TestCase
{

    use HelpersTestTrait;

    private ?Environment $environment;


    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $this->environment = new Environment('_compile', __DIR__ . '/../');
        $this->environment->setMinifyCSS(new CssMinify([]));
        $this->environment->setMinifyJS(new JsMinify([]));
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive($this->environment->getCompileDir(), true);
        $this->environment = null;
    }

    public function testLocalFile(): void
    {
        $reader = new Reader(new Asset('css', __DIR__ . '/../fixtures/test.css'), $this->environment);
        $this->assertSame("body{color:#00008b}\n", $reader->getContents());
    }

    public function testLocalFileNoMinify(): void
    {
        $reader = new Reader(
            new Asset('css', __DIR__ . '/../fixtures/test.css', [AssetOption::MINIFY => false]),
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
        $reader = new Reader(new Asset('css', '/'), $this->environment);
        $this->assertSame('', $reader->getContents());
    }

    public function testReturnGetContentFileExistsFalse(): void
    {
        $reader = new Reader(new Asset('css', '/test.css'), $this->environment);
        $this->assertSame('', $reader->getContents());
    }

    public function testWithReplaceRelativePath(): void
    {
        $reader = new Reader(
            new Asset('css', __DIR__ . '/../fixtures/sub/css/style.css', [AssetOption::MINIFY => false]),
            $this->environment
        );
        $this->assertSame(
            <<<CSS
@font-face {
    src:url('/fixtures/sub/fonts/font.eot') format('eot');
    src:url('/fixtures/sub/css/font2.eot');
    src:url('/font3.eot');
}

CSS
            ,
            $reader->getContents()
        );
    }

    public function testWithDisableReplaceRelativePath(): void
    {
        $reader = new Reader(
            new Asset(
                'css',
                __DIR__ . '/../fixtures/sub/css/style.css',
                [AssetOption::MINIFY => false, AssetOption::REPLACE_RELATIVE_URLS => false]
            ),
            $this->environment
        );
        $this->assertSame(
            <<<CSS
@font-face {
    src:url('./../fonts/font.eot?d7yf1v') format('eot');
    src:url('./font2.eot');
    src:url('/font3.eot');
}

CSS
            ,
            $reader->getContents()
        );
    }
}
