<?php

namespace Enjoys\AssetsCollector;

class Options
{
    protected array $options = [];

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function setOption(string $key, $value): Options
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function getOption(string $key, $defaults = null)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        return $defaults;
    }

    public function setOptions(array $options = []): Options
    {
        foreach ($options as $key => $value) {
            $this->setOption((string)$key, $value);
        }
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

}
