<?php

namespace Zimzat\QueryBuilder;

trait ListTrait
{
    abstract public function all(): array;

    public function getIterator(): \Generator
    {
        yield from $this->all();
    }
}
