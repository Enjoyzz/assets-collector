<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Extensions\Twig;

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\AssetType;
use Twig\Error\LoaderError;
use Twig\Extension\AbstractExtension;
use Twig\Loader\LoaderInterface;
use Twig\TwigFunction;

/**
 * Class AssetsExtension
 * Set assets from Twig. In the css example, but for js the same
 * {{  asset('css', [{0: '//cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.css', 'minify': false}]) }}
 * {{  asset('css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.css') }}
 * {{  asset('css', ['path/style1.css', 'style2.css']) }}
 *
 * Output
 * {{ eCSS() }}
 * {{ eJS() }}
 *
 * @package Enjoys\AssetsCollector\Extensions\Twig
 */
class AssetsExtension extends AbstractExtension
{
    /**
     * @var Assets
     */
    private Assets $assetsCollector;
    private ?LoaderInterface $loader;

    public function __construct(Assets $assetsCollector, ?LoaderInterface $loader = null)
    {
        $this->assetsCollector = $assetsCollector;
        $this->loader = $loader;
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
     * @throws LoaderError
     */
    public function asset(
        string|AssetType $type,
        array $paths = [],
        string $group = Assets::GROUP_COMMON,
        string $method = 'push'
    ): void {

        $type = AssetType::tryToAssetType($type);
        if ($type === null) {
            return;
        }

        $this->assetsCollector->add(
            $type,
            array_map(function ($item) {
                if ($this->loader === null) {
                    return $item;
                }
                /** @var string[] $path */
                $path = (array)$item;
                if ($this->loader->exists($path[0])) {
                    $path[0] = $this->loader->getSourceContext($path[0])->getPath();
                }
                return $path;
            }, $paths),
            $group,
            $method
        );
    }

    /**
     * @param string $group
     * @return string
     * @throws \Exception
     */
    public function getExternCss(string $group = Assets::GROUP_COMMON): string
    {
        return $this->assetsCollector->get(AssetType::CSS, $group);
    }

    /**
     * @param string $group
     * @return string
     * @throws \Exception
     */
    public function getExternJs(string $group = Assets::GROUP_COMMON): string
    {
        return $this->assetsCollector->get(AssetType::JS, $group);
    }

}
