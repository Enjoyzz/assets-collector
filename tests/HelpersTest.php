<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Helpers;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{

    use HelpersTestTrait;

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__.'/_compile', true);
    }

    public function dataErrorPath()
    {
        return [
            [__DIR__.'/_compile', true],
            ['.', false],
            ['..', false],
            ['...', false],
            [__DIR__.'/...', false],
            [__DIR__.'/..', false],
            [__DIR__.'/.', false],
            [__DIR__.'/_compile/.s', true],
          //  ['/_te<>mp', false]
        ];

    }

    /**
     * @dataProvider dataErrorPath
     */
    public function testCreateDirectoryError($path, $create)
    {
        if($create === false){
            $this->expectException(\Exception::class);
        }
        Helpers::createDirectory($path);
        $this->assertDirectoryExists($path);
    }

    public function testCreateSymlinkWhenUpFolderSymlynkAlreadyExist(){
        //$this->expectWarning();
        Helpers::createSymlink(__DIR__.'/_compile/fixtures/test.css', __DIR__.'/fixtures/test.css');
        Helpers::createSymlink(__DIR__.'/_compile/fixtures', __DIR__.'/fixtures');
        $this->assertTrue(true);
    }
}
