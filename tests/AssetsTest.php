<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Asset;
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Exception\NotAllowedMethods;
use PHPUnit\Framework\TestCase;

class AssetsTest extends TestCase
{
    use HelpersTestTrait;

    /**
     * @var Environment
     */
    private ?Environment $config;


    protected function setUp(): void
    {
        $this->config = new Environment(__DIR__ . '/_compile', __DIR__ . '/../');
        $this->config->setBaseUrl('/t')
            ->setStrategy(Assets::STRATEGY_MANY_FILES);
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive($this->config->getCompileDir(), true);

        $this->config = null;
    }

    public function testAdd()
    {
        $assets = new Assets($this->config);
        $assets->add(
            'css',
            [
                __DIR__ . '/../tests/fixtures/test.css',
                '//server.com/style.css',
                'http://secure.com/style.css',
                ['https://notsecure.com/style.css']
            ]
        );
        $assets->add(
            'js',
            [
                'not/exists.js',
                'http://localhost/script.js',
                ['https://localhost/script.js']
            ]
        );
        $this->assertSame(
            str_replace(
                "\r",
                "",
                <<<HTML
<link type='text/css' rel='stylesheet' href='/t/tests/fixtures/test.css' />
<link type='text/css' rel='stylesheet' href='http://server.com/style.css' />
<link type='text/css' rel='stylesheet' href='http://secure.com/style.css' />
<link type='text/css' rel='stylesheet' href='https://notsecure.com/style.css' />

HTML
            ),
            $assets->get('css')
        );


        $assets->getEnvironment()->setParamVersion('?v=');
        $assets->getEnvironment()->setVersion(1);
        $this->assertSame(
            str_replace(
                "\r",
                "",
                <<<HTML
<script src='http://localhost/script.js?v=1'></script>
<script src='https://localhost/script.js?v=1'></script>

HTML
            ),
            $assets->get('js')
        );
    }

    public function testAddedQueuePush()
    {
        $assets = new Assets($this->config);
        $assets->add(
            'css',
            [
                __DIR__ . '/../tests/fixtures/test.css',
                __DIR__ . '/../tests/fixtures/test2.css',
            ]
        );
        $assets->add(
            'css',
            [
                __DIR__ . '/../tests/fixtures/test3.css',
            ]
        );

        $this->assertSame(
            str_replace(
                "\r",
                "",
                <<<HTML
<link type='text/css' rel='stylesheet' href='/t/tests/fixtures/test.css' />
<link type='text/css' rel='stylesheet' href='/t/tests/fixtures/test2.css' />
<link type='text/css' rel='stylesheet' href='/t/tests/fixtures/test3.css' />

HTML
            ),
            $assets->get('css')
        );
    }

    public function testAddedQueueUnshift()
    {
        $assets = new Assets($this->config);
        $assets->add(
            'css',
            [
                __DIR__ . '/../tests/fixtures/test.css',
                __DIR__ . '/../tests/fixtures/test2.css',
            ],
            'test',
            'unshift'
        );
        $assets->add(
            'css',
            [
                __DIR__ . '/../tests/fixtures/test3.css',
            ],
            'test',
            'unshift'
        );

        $this->assertSame(
            str_replace(
                "\r",
                "",
                <<<HTML
<link type='text/css' rel='stylesheet' href='/t/tests/fixtures/test3.css' />
<link type='text/css' rel='stylesheet' href='/t/tests/fixtures/test.css' />
<link type='text/css' rel='stylesheet' href='/t/tests/fixtures/test2.css' />

HTML
            ),
            $assets->get('css', 'test')
        );
    }

    public function testInvalidMethodAddToCollection()
    {
        $this->expectException(NotAllowedMethods::class);
        $assets = new Assets($this->config);
        $assets->add(
            'css',
            [
                __DIR__ . '/../tests/fixtures/test.css',
            ],
            'test',
            'invalid'
        );
    }


    public function testAddSameAssetsInDifferentCallAdd()
    {
        $assets = new Assets($this->config);
        $assets->add(
            'css',
            [
                __DIR__ . '/../tests/fixtures/test.css'
            ]
        );
        $assets->add(
            'css',
            [
                __DIR__ . '/../tests/fixtures/test.css'
            ]
        );
        $this->assertSame(
            str_replace(
                "\r",
                "",
                <<<HTML
<link type='text/css' rel='stylesheet' href='/t/tests/fixtures/test.css' />

HTML
            ),
            $assets->get('css')
        );
    }

    public function testAttributes()
    {
        $_SERVER['HTTP_SCHEME'] = 'https';
        $assets = new Assets($this->config);
        $assets->add(
            'js',
            [
                [
                    '//cdnjs.cloudflare.com/ajax/libs/require.js/2.3.6/require.min.js',
                    Asset::ATTRIBUTES => [
                        'data-main' => './main.js'
                    ]
                ]
            ]
        );
        $this->assertSame(
            str_replace(
                "\r",
                "",
                <<<HTML
<script data-main='./main.js' src='https://cdnjs.cloudflare.com/ajax/libs/require.js/2.3.6/require.min.js'></script>

HTML
            ),
            $assets->get('js')
        );
        unset($_SERVER['HTTP_SCHEME']);
    }

    public function testAttributesWithoutValue()
    {
        $_SERVER['HTTP_SCHEME'] = 'https';
//        $this->config->setBaseUrl('/t')
//            ->setStrategy(Assets::STRATEGY_ONE_FILE);
        $assets = new Assets($this->config);
        $assets->add(
            'js',
            [
                [
                    'local:/require.min.js',
                    Asset::ATTRIBUTES => [
                        'attr_wo_value' => null,
                        'attr_wo_value2',
                    ]
                ]
            ]
        );
        $this->assertSame(
            str_replace(
                "\r",
                "",
                <<<HTML
<script attr_wo_value attr_wo_value2 src='/require.min.js'></script>

HTML
            ),
            $assets->get('js')
        );
        unset($_SERVER['HTTP_SCHEME']);
    }
}
