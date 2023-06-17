<?php

namespace PEF\Types;

use PEF\Builder\FieldBuilder;
use PEF\Interfaces\ValueScanner;
use PEF\ValueScanner\StringScanner;
use ReflectionClass;

class KnownField
{
    public const TYPE_STRING = 'string';

    public static function string(string $name): FieldBuilder
    {
        return (new FieldBuilder())
            ->setName($name)
            ->setType(static::TYPE_STRING)
            ->setValueScanner(new StringScanner());
    }

    public static function other(string $name, ValueScanner $typ): FieldBuilder
    {
        $rt = new ReflectionClass($typ);
        return (new FieldBuilder())
            ->setName($name)
            ->setType($rt->getName())
            ->setValueScanner($typ);
    }
}