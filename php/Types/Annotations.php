<?php

namespace PEF\Types;

use PEF\Interfaces\Annotation;

class Annotations implements \PEF\Interfaces\Annotations
{

    /**
     * @var array|Annotation[] List of Field
     */
    protected array $annotations;

    /**
     * @param array $annotations
     */
    public function __construct(Annotation ...$annotations)
    {
        $this->annotations = $annotations;
    }

    public function collection(): array
    {
        return $this->annotations;
    }
}