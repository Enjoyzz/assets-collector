<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Helpers;
use Exception;
use PHPUnit\Framework\TestCase;

use function Enjoys\FileSystem\makeSymlink;

class HelpersTest extends TestCase
{

    use HelpersTestTrait;

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__ . '/_compile', true);
    }

    public function dataErrorPath()
    {
        return [
            [__DIR__ . '/_compile', true],
            ['.', false],
            ['..', false],
            ['...', false],
            [__DIR__ . '/...', false],
            [__DIR__ . '/..', false],
            [__DIR__ . '/.', false],
            [__DIR__ . '/_compile/.s', true],
            //  ['/_te<>mp', false]
        ];
    }

    /**
     * @dataProvider dataErrorPath
     */
    public function testCreateDirectoryError($path, $create)
    {
        if ($create === false) {
            $this->expectException(Exception::class);
        }
        Helpers::createDirectory($path);
        $this->assertDirectoryExists($path);
    }

    /**
     * @throws Exception
     */
    public function testCreateSymlinkWhenUpFolderSymlynkAlreadyExist(): void
    {
        $this->expectNotToPerformAssertions();
        makeSymlink(__DIR__ . '/_compile/fixtures/test.css', __DIR__ . '/fixtures/test.css');
        makeSymlink(__DIR__ . '/_compile/fixtures', __DIR__ . '/fixtures');
    }
}
