<?php

namespace Zimzat\QueryBuilder;

class TableList implements Sql, \IteratorAggregate
{
    use ListTrait;

    /** @var TableReference[] */
    protected array $items = [];

    public function all(): array
    {
        return $this->items;
    }

    public function add(TableReference $table): self
    {
        $this->items[] = $table;
        return $this;
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        return new SqlQueryValue(
            implode(', ', array_map(
                static fn (TableReference $table) => $table->getAlias() ?? (
                    $table instanceof Table
                        ? $table->getTable()
                        : throw new \UnexpectedValueException('Could not determine table reference')
                ),
                $this->items,
            )),
        );
    }
}
