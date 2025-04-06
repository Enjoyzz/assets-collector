<?php

namespace Enjoys\AssetsCollector;

enum AssetType: string
{
    case CSS = 'css';
    case JS = 'js';



    public static function tryToAssetType(AssetType|string $value): ?AssetType
    {
        if ($value instanceof AssetType){
            return $value;
        }

        return self::tryFrom(strtolower($value));
    }

    public function getSrcAttribute(): string
    {
        return match($this) {
            AssetType::CSS => 'href',
            AssetType::JS => 'src'
        };
    }
}
