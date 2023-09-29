<?php

declare(strict_types=1);

namespace Enjoys\AssetsCollector\Content\Minify\Adapters;

use Enjoys\AssetsCollector\MinifyInterface;
use tubalmartin\CssMin\Minifier as CSSmin;

/**
 * Class CssMinify
 * @package Enjoys\AssetsCollector\Content\Minify\Adapters
 */
class CssMinify implements MinifyInterface
{
    private string $content = '';
    private array $options;

    /**
     * CssMinify constructor.
     * @param array $minifyOptions
     */
    public function __construct(array $minifyOptions = [])
    {
        $this->options = $minifyOptions;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        $compressor = new CSSMin();
        $compressor->keepSourceMapComment((bool)($this->options['keepSourceMapComment'] ?? false));
        // Remove important comments from output.
        $compressor->removeImportantComments((bool)($this->options['removeImportantComments'] ?? true));
        // Split long lines in the output approximately every 1000 chars.
        $compressor->setLineBreakPosition((int)($this->options['setLineBreakPosition'] ?? 1000));
        $compressor->setMaxExecutionTime((int)($this->options['setMaxExecutionTime'] ?? 60));
        // Override any PHP configuration options before calling run() (optional)
        $compressor->setMemoryLimit((string)($this->options['setMemoryLimit'] ?? '256M'));
        $compressor->setPcreBacktrackLimit((int)($this->options['setPcreBacktrackLimit'] ?? 1000000));
        $compressor->setPcreRecursionLimit((int)($this->options['setPcreRecursionLimit'] ?? 500000));
        // Compress the CSS code!
        return $compressor->run($this->content);
    }
}
