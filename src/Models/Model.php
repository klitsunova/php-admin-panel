<?php

namespace App\Models;

abstract class Model
{
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable ?? [])) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __get(string $key)
    {
        if (method_exists($this, 'get' . ucfirst($key) . 'Attribute')) {
            return $this->{'get' . ucfirst($key) . 'Attribute'}();
        }

        return $this->getAttribute($key);
    }

    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
}
