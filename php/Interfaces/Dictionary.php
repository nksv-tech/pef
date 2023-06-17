<?php

namespace PEF\Interfaces;

interface Dictionary
{
    public function set(string $key, mixed $value): Dictionary;

    public function get(string $key): mixed;
    
    public function all(): array;
}