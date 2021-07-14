<?php

namespace Zimzat\QueryBuilder;

class Limit implements Sql
{
    protected ?int $count = null;

    protected ?int $offset = null;

    public function set(?int $count = null, ?int $offset = null): self
    {
        $this->count = $count;
        $this->offset = $offset;
        return $this;
    }

    public function isSet(): bool
    {
        return $this->count !== null;
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        if ($this->count === null) {
            return new SqlQueryValue('');
        }

        if ($this->offset !== null) {
            return new SqlQueryValue('LIMIT ? OFFSET ?', [$this->count, $this->offset]);
        }

        return new SqlQueryValue('LIMIT ?', [$this->count]);
    }
}
