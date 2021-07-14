<?php

namespace Zimzat\QueryBuilder;

class ColumnList implements Sql, \IteratorAggregate
{
    use ListTrait;

    protected array $items = [];

    public function all(): array
    {
        return $this->items;
    }

    public function add(Column $column): self
    {
        $this->items[] = $column;
        return $this;
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        return new SqlQueryValue(
            '?' . str_repeat(', ?', count($this->items) - 1),
            array_map(static fn (Column $column) => $column->getName(), $this->items),
        );
    }
}
