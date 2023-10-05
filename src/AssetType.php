<?php

namespace Enjoys\AssetsCollector;

enum AssetType: string
{
    case CSS = 'css';
    case JS = 'js';

    public static function convertToAssetType(AssetType|string $value): AssetType
    {
        if ($value instanceof AssetType){
            return $value;
        }

        return match ($value) {
            'CSS', 'Css', 'css' => AssetType::CSS,
            'JS', 'Js', 'js' => AssetType::JS,
        };
    }

    public function getSrcAttribute(): string
    {
        return match($this) {
            AssetType::CSS => 'href',
            AssetType::JS => 'src'
        };
    }
}