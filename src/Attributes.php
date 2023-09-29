<?php

declare(strict_types=1);


namespace Enjoys\AssetsCollector;


final class Attributes
{
    /**
     * @var array<array-key, string|null|false>|null
     */
    private ?array $attributes;

    /**
     * @param array<array-key, string|null|false>|null $attributes
     */
    public function __construct(?array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function __toString(): string
    {
        if ($this->attributes === null) {
            return '';
        }
        $result = [];
        foreach ($this->attributes as $key => $value) {
            if ($value === false) {
                continue;
            }

            if (is_int($key)) {
                $key = $value;
                $value = null;
            }
            if ($key === null || $key === '') {
                continue;
            }

            if ($value === null) {
                $result[] = sprintf("%s", $key);
                continue;
            }
            $result[] = sprintf("%s='%s'", $key, $value);
        }

        return (empty($result)) ? '' : ' ' . implode(" ", $result);
    }
}
