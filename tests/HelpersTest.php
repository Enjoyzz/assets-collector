<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\Helpers;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{

    use HelpersTestTrait;

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive(__DIR__.'/_temp');
    }

    public function dataErrorPath()
    {
        return [
            [__DIR__.'/_temp', true],
            ['.', false],
            ['..', false],
            ['...', false],
            [__DIR__.'/...', false],
            [__DIR__.'/..', false],
            [__DIR__.'/.', false],
            [__DIR__.'/_temp/.s', true],
            ['/_te<>mp', false]
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
}
