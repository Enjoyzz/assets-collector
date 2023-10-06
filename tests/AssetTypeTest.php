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
            AssetType::convertToAssetType('Css')
        );
        $this->assertSame(
            AssetType::CSS,
            AssetType::convertToAssetType('CSS')
        );
        $this->assertSame(
            AssetType::CSS,
            AssetType::convertToAssetType('css')
        );
        $this->assertSame(
            AssetType::JS,
            AssetType::convertToAssetType('Js')
        );
        $this->assertSame(
            AssetType::JS,
            AssetType::convertToAssetType('JS')
        );
        $this->assertSame(
            AssetType::JS,
            AssetType::convertToAssetType('js')
        );
        $this->assertSame(
            AssetType::JS,
            AssetType::convertToAssetType(AssetType::JS)
        );
    }

    public function testGetSrcAttribute()
    {
        $this->assertSame(
            'src',
            AssetType::JS->getSrcAttribute()
        );
        $this->assertSame(
            'href',
            AssetType::CSS->getSrcAttribute()
        );
    }
}
