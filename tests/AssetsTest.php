<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;

class AssetsTest extends TestCase
{
    /**
     * @var Environment
     */
    private Environment $config;


    protected function setUp(): void
    {
        $this->config = new Environment(__DIR__ . '/_compile', __DIR__ . '/../');
        $this->config->setBaseUrl('/t')
            ->setStrategy(Assets::STRATEGY_MANY_FILES);
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
}
