<?php

namespace PEF\Types;

use PEF\Builder\FieldBuilder;

class Fields implements \PEF\Interfaces\Fields
{
    /**
     * @var array|Field[] List of Field
     */
    protected array $fields;

    /**
     * @param array $fields
     */
    public function __construct(FieldBuilder ...$fields)
    {
        foreach ($fields as $field) {
            $this->fields[] = $field->build();
        }
    }

    public function collection(): array
    {
        return $this->fields;
    }
}