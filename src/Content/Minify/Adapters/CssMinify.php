<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify\Adapters;

use Enjoys\AssetsCollector\Content\Minify\MinifyInterface;
use tubalmartin\CssMin\Minifier as CSSmin;

/**
 * Class CssMinify
 * @package Enjoys\AssetsCollector\Content\Minify\Adapters
 */
class CssMinify implements MinifyInterface
{
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     * @todo вынести все настройки отдельно
     */
    public function getContent(): string
    {
        $compressor = new CSSMin();
        $compressor->keepSourceMapComment(false);
        // Remove important comments from output.
        $compressor->removeImportantComments();
        // Split long lines in the output approximately every 1000 chars.
        $compressor->setLineBreakPosition(1000);
        // Override any PHP configuration options before calling run() (optional)
        $compressor->setMemoryLimit('256M');
        $compressor->setMaxExecutionTime(120);
        $compressor->setPcreBacktrackLimit(3000000);
        $compressor->setPcreRecursionLimit(150000);

        // Compress the CSS code!
        return $compressor->run($this->content);
    }
}
