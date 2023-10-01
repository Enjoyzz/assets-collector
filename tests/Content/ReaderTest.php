<?php

namespace Tests\Enjoys\AssetsCollector\Content;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Content\Minify\Adapters\CssMinify;
use Enjoys\AssetsCollector\Content\Minify\Adapters\JsMinify;
use Enjoys\AssetsCollector\Content\Reader;
use Enjoys\AssetsCollector\Environment;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use JShrink\Minifier;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Tests\Enjoys\AssetsCollector\ArrayLogger;
use Tests\Enjoys\AssetsCollector\HelpersTestTrait;
use tubalmartin\CssMin\Minifier as CSSmin;

class ReaderTest extends TestCase
{

    use HelpersTestTrait;

    private ?Environment $environment;
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;


    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $this->environment = new Environment('_compile', __DIR__ . '/../');
        $this->environment->setCssMinify(function ($content){
            $compressor = new CSSMin();
            return $compressor->run($content);
        });
        $this->environment->setJsMinify(function ($content){
            return (string)Minifier::minify(
                $content,
                [
                    'flaggedComments' =>  false
                ]
            );
        });

        $this->httpClient =  new Client(
            [
                'verify' => false,
                'allow_redirects' => true,
                'headers' => [
                    'User-Agent' =>
                        'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
                ]
            ]
        );

        $this->requestFactory = new HttpFactory();
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive($this->environment->getCompileDir(), true);
        $this->environment = null;
    }

    public function testLocalFile(): void
    {
        $reader = new Reader(new Asset('css', __DIR__ . '/../fixtures/test.css'), $this->environment);
        $reader->minify();
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
        $reader->replaceRelativeUrlsAndCreatedSymlinks()->minify();
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

    public function xtestWithReplaceRelativeUrlWithOneFileStrategy(): void
    {
      //  $this->environment->setStrategy(Assets::STRATEGY_ONE_FILE);
        $reader = new Reader(
            new Asset('css', 'https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.css', [AssetOption::MINIFY => false]),
            $this->environment
        );
        $this->assertStringContainsString('https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/fonts/fontawesome-webfont.eot', $reader->getContents());
    }

    public function testWithDisableReplaceRelativePath(): void
    {
        $reader = new Reader(
            new Asset(
                AssetType::CSS,
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

    public function testRemoteUrlWithReadHttpClientSuccess()
    {
        $this->environment
            ->setRequestFactory($this->requestFactory)
            ->setHttpClient($this->httpClient)
        ;
        $reader = new Reader(
            new Asset(
                AssetType::CSS,
                'https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.css',
                [AssetOption::MINIFY => false, AssetOption::REPLACE_RELATIVE_URLS => false]
            ),
            $this->environment
        );
        $this->assertStringContainsString('Bootstrap  v5.2.0 (https://getbootstrap.com/)', $reader->getContents());
    }

    public function testRemoteUrlWithReadHttpClientFailed()
    {
        $this->environment
            ->setLogger($logger = new ArrayLogger())
            ->setRequestFactory($this->requestFactory)
            ->setHttpClient($this->httpClient)
        ;
        $reader = new Reader(
            new Asset(
                AssetType::CSS,
                'https://cdn.jsdelivr.net/invalid_url',
                [AssetOption::MINIFY => false, AssetOption::REPLACE_RELATIVE_URLS => false]
            ),
            $this->environment
        );
        $this->assertNotEmpty($logger->getLog('notice'));
        $this->assertSame('', $reader->getContents());
    }

    public function testRemoteUrlWithReadFileGetContentsSuccess()
    {
        $reader = new Reader(
            new Asset(
                AssetType::CSS,
                'https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.css',
                [AssetOption::MINIFY => false, AssetOption::REPLACE_RELATIVE_URLS => false]
            ),
            $this->environment
        );
        $this->assertStringContainsString('Bootstrap  v5.2.0 (https://getbootstrap.com/)', $reader->getContents());
    }

    public function testRemoteUrlWithReadFileGetContentsFailed()
    {
        $this->environment
            ->setLogger($logger = new ArrayLogger())
        ;
        $reader = new Reader(
            new Asset(
                AssetType::CSS,
                'https://cdn.jsdelivr.net/invalid_url',
                [AssetOption::MINIFY => false, AssetOption::REPLACE_RELATIVE_URLS => false]
            ),
            $this->environment
        );
        $this->assertNotEmpty($logger->getLog('notice'));
        $this->assertStringContainsString('', $reader->getContents());
    }
}
