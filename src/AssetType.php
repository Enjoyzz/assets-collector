<?php

namespace Enjoys\AssetsCollector;

enum AssetType: string
{
    case CSS = 'css';
    case JS = 'js';

    public static function normalize(AssetType|string $value): AssetType
    {
        if ($value instanceof AssetType){
            return $value;
        }

        return match ($value) {
            'CSS', 'Css', 'css' => AssetType::CSS,
            'JS', 'Js', 'js' => AssetType::JS,
        };
    }
}
