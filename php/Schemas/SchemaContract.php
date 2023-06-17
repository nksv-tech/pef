<?php

namespace PEF\Schemas;

use PEF\Interfaces\Fields;
use PEF\Interfaces\Schema;
use PEF\Types\Annotations;
use PEF\Types\Dialect;
use PEF\Types\KnownField;
use PEF\Types;

class SchemaContract implements Schema
{
    public function getAnnotations(): Annotations
    {
        return new Annotations();
    }

    public function getFields(): Fields
    {
        return new Types\Fields(
            KnownField::string('hello')
                ->optional()
                ->nillable()
                ->setSchemaType(Dialect::PSQL, 'text')
                ->setSchemaType(Dialect::MYSQL, 'varchar(255)')
                ->setAnnotations()
        );
    }
}