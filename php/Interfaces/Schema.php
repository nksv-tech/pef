<?php

namespace PEF\Interfaces;

interface Schema
{
    public function getAnnotations(): Annotations;

    public function getFields(): Fields;
}