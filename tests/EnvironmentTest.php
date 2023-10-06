<?php

declare(strict_types=1);

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\AssetType;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Strategy;
use Enjoys\AssetsCollector\Strategy\ManyFilesStrategy;
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
        if (is_array($expect)) {
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
            ['/', realpath('/')],
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
            ['/../dir', __DIR__ . '/dir'],
            ['/../../dir', __DIR__ . '/dir'],
            ['../dir', __DIR__ . '/dir'],
            ['dir', __DIR__ . '/dir'],
            ['dir/dir2', __DIR__ . '/dir/dir2'],
            ['dir/dir2/', __DIR__ . '/dir/dir2'],
            [__DIR__ . '/../dir/', __DIR__ . '/dir'],
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


    public function testSetLogger()
    {
        $environment = new Environment();
        $environment->setLogger(new NullLogger());
        $this->assertInstanceOf(LoggerInterface::class, $environment->getLogger());
    }

    public function testGetVersion()
    {
        $environment = new Environment();
        $this->assertSame(null, $environment->getVersion());
        $environment->setVersion('1.0.0');
        $this->assertSame('1.0.0', $environment->getVersion());
    }

    public function testGetParamVersion()
    {
        $environment = new Environment();
        $this->assertSame('v', $environment->getParamVersion());
        $environment->setParamVersion('version');
        $this->assertSame('version', $environment->getParamVersion());
    }

    public function testSetRenderer()
    {
        $environment = new Environment();
        $renderer = function ($assets) {
            return 'my render';
        };
        $environment->setRenderer(AssetType::CSS, $renderer);
        $this->assertSame($renderer([]), $environment->getRenderer(AssetType::CSS)->render([]));
    }

    public function testGetStrategy()
    {
        $environment = new Environment();
        $this->assertSame(ManyFilesStrategy::class, $environment->getStrategy()::class);

        $strategy = new class implements Strategy {

            public function getAssets(AssetType $type, array $assetsCollection, Environment $environment): array
            {
                return [];
            }
        };
        $environment->setStrategy($strategy);
        $this->assertSame($strategy, $environment->getStrategy());
    }

}
