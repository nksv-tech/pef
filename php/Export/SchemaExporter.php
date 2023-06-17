<?php

namespace PEF\Export;

use PEF\Interfaces\Schema;
use ReflectionClass;

class SchemaExporter implements Export
{
    protected array $schemes = [];

    public function __construct(
        Schema ...$schema
    )
    {
        $this->schemes = $schema;
    }

    public function export(): array
    {
        $schemas = [];
        foreach ($this->schemes as $schema) {
            $export = [
                'classInfo' => (object)$this->classInfo($schema),
                'annotations' => (object)$schema->getAnnotations()->collection()
            ];

            foreach ($schema->getFields()->collection() as $field) {
                $export['fields'][] = [
                    'name' => $field->getName(),
                    'type' => $field->getType(),
                    'optional' => $field->isOptional(),
                    'nillable' => $field->isNillable(),
                    'comment' => $field->getComment(),
                    'schemaType' => (object)$field->getSchemaType()->all(),
                    'valueScanner' => [
                        'classInfo' => (object)$this->classInfo($field->getValueScanner())
                    ],
                    'annotations' => (object)$field->getAnnotations()->collection()
                ];
            }

            $schemas[] = $export;
        }

        return ['schemas' => $schemas];
    }

    protected function classInfo($class): array
    {
        $refClass = new ReflectionClass($class);
        return [
            'className' => $refClass->getShortName(),
            'classNameFull' => $refClass->getName(),
            'namespace' => $refClass->getNamespaceName(),
            'interfaces' => $refClass->getInterfaceNames(),
        ];
    }
}