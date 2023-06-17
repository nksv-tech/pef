<?php

namespace PEF\Types;


use PEF\Interfaces\Annotations;
use PEF\Interfaces\Dictionary;
use PEF\Interfaces\ValueScanner;

class Field implements \PEF\Interfaces\Field
{

    public function __construct(
        /**
         * @var string
         * Field name
         */
        protected string       $name,
        /**
         * @var string
         * Field type (string,bool,Class and more...)
         */
        protected string       $type,
        /**
         * @var ValueScanner
         * Field type valuer
         */
        protected ValueScanner $valueScanner,
        /**
         * @var bool
         * Optional field. If set false then schema set NOT NULL field on driver
         */
        protected bool         $optional = false,
        /**
         * @var bool
         * Nillable field. If set true then generation struct set ?type and default value NULL in driver
         */
        protected bool         $nillable = false,
        /**
         * @var string
         * Comment string will be writing to DB driver column
         */
        protected string       $comment = '',
        /**
         * @var Dictionary|null
         */
        protected ?Dictionary  $schemaType = null,
        /**
         * @var Annotations|null
         */
        protected ?Annotations $annotations = null
    )
    {
        $this->schemaType = $this->schemaType ?? new \PEF\Types\Dictionary();
        $this->annotations = $this->annotations ?? new \PEF\Types\Annotations();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return ValueScanner
     */
    public function getValueScanner(): ValueScanner
    {
        return $this->valueScanner;
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return bool
     */
    public function isNillable(): bool
    {
        return $this->nillable;
    }

    public function getSchemaType(): Dictionary
    {
        return $this->schemaType;
    }

    public function getAnnotations(): Annotations
    {
        return $this->annotations;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}