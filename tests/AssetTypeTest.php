<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\AssetType;
use PHPUnit\Framework\TestCase;

class AssetTypeTest extends TestCase
{

    public function testConvertToAssetType()
    {
        $this->assertSame(
            AssetType::CSS,
            AssetType::tryToAssetType('Css')
        );
        $this->assertSame(
            AssetType::CSS,
            AssetType::tryToAssetType('CSS')
        );
        $this->assertSame(
            AssetType::CSS,
            AssetType::tryToAssetType('css')
        );
        $this->assertSame(
            AssetType::JS,
            AssetType::tryToAssetType('Js')
        );
        $this->assertSame(
            AssetType::JS,
            AssetType::tryToAssetType('JS')
        );
        $this->assertSame(
            AssetType::JS,
            AssetType::tryToAssetType('js')
        );
        $this->assertSame(
            AssetType::JS,
            AssetType::tryToAssetType(AssetType::JS)
        );
    }

    public function testGetSrcAttribute()
    {
        $this->assertSame(
            'src',
            AssetType::JS->htmlAttribute()
        );
        $this->assertSame(
            'href',
            AssetType::CSS->htmlAttribute()
        );
    }
}
