<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify\Adapters;

use Enjoys\AssetsCollector\Content\Minify\MinifyInterface;
use Enjoys\Traits\Options;
use tubalmartin\CssMin\Minifier as CSSmin;

/**
 * Class CssMinify
 * @package Enjoys\AssetsCollector\Content\Minify\Adapters
 */
class CssMinify implements MinifyInterface
{
    use Options;

    private string $content;

    /**
     * CssMinify constructor.
     * @param string $content
     * @param array<mixed> $minifyOptions
     */
    public function __construct(string $content, array $minifyOptions)
    {
        $this->content = $content;
        $this->setOptions($minifyOptions);
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        $compressor = new CSSMin();
        $compressor->keepSourceMapComment($this->getOption('keepSourceMapComment', false));
        // Remove important comments from output.
        $compressor->removeImportantComments($this->getOption('removeImportantComments', true));
        // Split long lines in the output approximately every 1000 chars.
        $compressor->setLineBreakPosition($this->getOption('setLineBreakPosition', 1000));
        $compressor->setMaxExecutionTime($this->getOption('setMaxExecutionTime', 60));
        // Override any PHP configuration options before calling run() (optional)
        $compressor->setMemoryLimit($this->getOption('setMemoryLimit', '256M'));
        $compressor->setPcreBacktrackLimit($this->getOption('setPcreBacktrackLimit', 1000000));
        $compressor->setPcreRecursionLimit($this->getOption('setPcreRecursionLimit', 500000));
        // Compress the CSS code!
        return $compressor->run($this->content);
    }
}
