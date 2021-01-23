<?php
declare(strict_types=1);

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class EnvironmentTest extends TestCase
{

    /**
     * @dataProvider dataTestSetProjectDir
     */
    public function testSetProjectDir($path, $expect): void
    {
        if(is_array($expect)){
            $this->expectException($expect[0]);
        }
        $environment = new Environment('', $path);
        $this->assertSame($expect, $environment->getProjectDir());
    }

    /**
     * @return array[]
     */
    public function dataTestSetProjectDir(): array
    {
        return [
            ['', realpath('')],
            [__DIR__, __DIR__],
            ['/', '/'],
            ['0', [\Exception::class]],
        ];
    }

    /**
     * @dataProvider dataTestSetCompileDir
     */
    public function testSetCompileDir($path, $expect): void
    {
        $environment = new Environment($path, __DIR__);
        $this->assertSame($expect, $environment->getCompileDir());
    }

    /**
     * @return array[]
     */
    public function dataTestSetCompileDir(): array
    {
        return [
            ['', __DIR__],
            ['/', __DIR__],
            ['/../dir', __DIR__.'/dir'],
            ['/../../dir', __DIR__.'/dir'],
            ['../dir', __DIR__.'/dir'],
            ['dir', __DIR__.'/dir'],
            ['dir/dir2', __DIR__.'/dir/dir2'],
            ['dir/dir2/', __DIR__.'/dir/dir2'],
            [__DIR__.'/../dir/', __DIR__.'/dir'],
        ];
    }

    /**
     * @dataProvider dataTestSetBaseUrl
     */
    public function testSetBaseUrl($path, $expect)
    {
        $environment = new Environment();
        $environment->setBaseUrl($path);
        $this->assertSame($expect, $environment->getBaseUrl());
    }

    /**
     * @return array[]
     */
    public function dataTestSetBaseUrl(): array
    {
        return [
            ['.', '.'],
            ['/', ''],
            ['/sub/', '/sub'],
            ['../sub////', '../sub'],
        ];
    }

    public function testSetCacheTime()
    {
        $environment = new Environment();
        $environment->setCacheTime(999);
        $this->assertSame(999, $environment->getCacheTime());
    }


    public function testGetRender()
    {
        $environment = new Environment();
        $this->assertSame(Assets::RENDER_HTML, $environment->getRender());
    }

    public function testSetLogger()
    {
        $environment = new Environment();
        $environment->setLogger(new NullLogger());
        $this->assertInstanceOf(LoggerInterface::class, $environment->getLogger());
    }

}
