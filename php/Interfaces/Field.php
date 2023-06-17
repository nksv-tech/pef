<?php

namespace PEF\Interfaces;

interface Field
{
    public function getName(): string;

    public function getType(): string;

    public function getComment(): string;

    public function getValueScanner(): ValueScanner;

    public function getSchemaType(): ?Dictionary;

    public function isOptional(): bool;

    public function isNillable(): bool;
}