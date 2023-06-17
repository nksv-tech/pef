<?php

namespace PEF\Types;

class Dictionary implements \PEF\Interfaces\Dictionary
{
    protected array $dictionary = [];

    public function __construct()
    {
    }

    public function set(string $key, mixed $value): self
    {
        $this->dictionary[$key] = $value;
        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->dictionary[$key];
    }

    public function all(): array
    {
        return $this->dictionary;
    }
}