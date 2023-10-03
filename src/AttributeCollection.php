<?php

declare(strict_types=1);


namespace Enjoys\AssetsCollector;


final class AttributeCollection
{

    /**
     * @param array<array-key, string|null|false> $attributes
     */
    public function __construct(private array $attributes = [])
    {
    }

    public function isEmpty(): bool
    {
        return $this->attributes === [];
    }

    public function set(string $key, string|null|false $value, bool $replace = false): AttributeCollection
    {
        if ($replace === false && array_key_exists($key, $this->attributes)) {
            return $this;
        }

        $this->attributes[$key] = $value;
        return $this;
    }

    public function get(string $key): string|null|false
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        return false;
    }

    public function __toString(): string
    {
        if ($this->isEmpty()) {
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


    /**
     * @return array<array-key, string|null|false>
     */
    public function getArray(): array
    {
        return $this->attributes;
    }


}
