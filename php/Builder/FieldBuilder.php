<?php

namespace PEF\Builder;

use PEF\Interfaces\Annotation;
use PEF\Interfaces\Annotations;
use PEF\Interfaces\ValueScanner;
use PEF\Types\Dictionary;
use PEF\Types\Field;

class FieldBuilder
{
    /**
     * @var string
     * Field name
     */
    protected string $name = '';
    /**
     * @var string
     * Field type (string,bool,Class and more...)
     */
    protected string $type = '';
    /**
     * @var ValueScanner
     * Field type valuer
     */
    protected ValueScanner $valueScanner;
    /**
     * @var bool
     * Optional field. If set false then schema set NOT NULL field on driver
     */
    protected bool $optional = false;
    /**
     * @var bool
     * Nillable field. If set true then generation struct set ?type and default value NULL in driver
     */
    protected bool $nillable = false;
    /**
     * @var string
     */
    protected string $comment = '';
    /**
     * @var \PEF\Interfaces\Dictionary
     */
    protected \PEF\Interfaces\Dictionary $schemaType;
    /**
     * @var Annotations
     */
    protected Annotations $annotations;

    public function __construct()
    {
        $this->schemaType = new Dictionary();
    }

    public static function create(): FieldBuilder
    {
        return new self();
    }

    public function build(): Field
    {
        return new Field(
            name: $this->name,
            type: $this->type,
            valueScanner: $this->valueScanner,
            optional: $this->optional,
            nillable: $this->nillable,
            comment: $this->comment,
            schemaType: $this->schemaType
        );
    }

    /**
     * @param string $name
     * @return FieldBuilder
     */
    public function setName(string $name): FieldBuilder
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $type
     * @return FieldBuilder
     */
    public function setType(string $type): FieldBuilder
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $comment
     * @return FieldBuilder
     */
    public function setComment(string $comment): FieldBuilder
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return FieldBuilder
     */
    public function setSchemaType(string $key, string $value): FieldBuilder
    {
        $this->schemaType->set($key, $value);
        return $this;
    }

    /**
     * @param Annotation ...$annotations
     * @return $this
     */
    public function setAnnotations(Annotation ...$annotations): FieldBuilder
    {
        $this->annotations = new \PEF\Types\Annotations(...$annotations);
        return $this;
    }

    /**
     * @param mixed $valueScanner
     * @return FieldBuilder
     */
    public function setValueScanner(ValueScanner $valueScanner): FieldBuilder
    {
        $this->valueScanner = $valueScanner;
        return $this;
    }

    /**
     * @return FieldBuilder
     */
    public function optional(): FieldBuilder
    {
        $this->optional = true;
        return $this;
    }

    /**
     * @return FieldBuilder
     */
    public function nillable(): FieldBuilder
    {
        $this->nillable = true;
        return $this;
    }
}