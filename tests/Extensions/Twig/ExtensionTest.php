<?php

declare(strict_types=1);


namespace Tests\Enjoys\AssetsCollector\Extensions\Twig;


use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use Enjoys\AssetsCollector\Extensions\Twig\AssetsExtension;
use Tests\Enjoys\AssetsCollector\HelpersTestTrait;
use Twig\Test\IntegrationTestCase;

final class ExtensionTest extends IntegrationTestCase
{

    protected function getFixturesDir()
    {
        return __DIR__.'/fixtures';
    }

    protected function getExtensions()
    {
        $environment = new Environment('_compile', __DIR__ . '/../..');
        $environment->setBaseUrl('/foo');
        $assets = new Assets($environment);
        $extension = new AssetsExtension($assets);
        $extension->asset('css', ['//google.com', '//yandex.ru']);
        $extension->asset('js', ['//google.com']);
        return [
            $extension
        ];
    }
}
