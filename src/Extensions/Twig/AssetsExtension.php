<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Extensions\Twig;

use Enjoys\AssetsCollector\Assets;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AssetsExtension
 * Set assets from Twig. In the css example, but for js the same
 * {{  asset('css', [{0: 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.css', 'minify': false}]) }}
 * {{  asset('css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.css') }}
 * {{  asset('css', ['path/style1.css', 'style2.css']) }}
 *
 * Output
 * {{ eCSS()|raw }}
 * {{ eJS()|raw }}
 *
 * @package Enjoys\AssetsCollector\Extensions\Twig
 */
class AssetsExtension extends AbstractExtension
{
    /**
     * @var Assets
     */
    private Assets $assetsCollector;

    public function __construct(Assets $assetsCollector)
    {
        $this->assetsCollector = $assetsCollector;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'asset']),
            new TwigFunction('eCSS', [$this, 'getExternCss'], ['is_safe' => ['html']]),
            new TwigFunction('eJS', [$this, 'getExternJs'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $type
     * @param array<string> $paths
     * @param string $namespace
     */
    public function asset(string $type, array $paths = [], string $namespace = Assets::NAMESPACE_COMMON): void
    {
        $this->assetsCollector->add($type, $paths, $namespace);
    }

    /**
     * @param string $namespace
     * @return string
     * @throws \Exception
     */
    public function getExternCss(string $namespace = Assets::NAMESPACE_COMMON): string
    {
        return $this->assetsCollector->get('css', $namespace);
    }

    /**
     * @param string $namespace
     * @return string
     * @throws \Exception
     */
    public function getExternJs(string $namespace = Assets::NAMESPACE_COMMON): string
    {
        return $this->assetsCollector->get('js', $namespace);
    }

}