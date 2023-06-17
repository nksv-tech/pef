<?php

namespace PEF\Interfaces;

interface ValueScanner
{
    /**
     * Сканирует из $src данные и возвращает готовый тип
     * @param mixed $src
     * @return mixed
     */
    public function scan(mixed $src): mixed;

    /**
     * Возвращает значение в виде, который понимает БД
     * @param mixed $src
     * @return mixed
     */
    public function value(mixed $src): mixed;
}