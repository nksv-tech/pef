<?php

namespace PEF\ValueScanner;

use PEF\Interfaces\ValueScanner;
use TypeError;
use function is_array;
use function is_string;

class StringScanner implements ValueScanner
{
    public function scan(mixed $src): mixed
    {
        switch ($src) {
            case null:
                return '';
            case is_string($src):
                return $src;
            case is_array($src):
                $chars = array_map("chr", $src);
                return join($chars);
            default:
                throw new TypeError('StringScanner - scan: unknown data type received');
        }
    }

    public function value(mixed $src): mixed
    {
        switch ($src) {
            case is_string($src):
                return $src;
            default:
                throw new TypeError('StringScanner - value: unknown data type received');
        }
    }
}