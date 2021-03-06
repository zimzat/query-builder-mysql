<?php

namespace Zimzat\QueryBuilder;

class Table implements Sql, TableReference
{
    protected string $table;

    protected ?string $alias;

    public function __construct(string $table, ?string $alias = null)
    {
        $this->table = $table;
        $this->alias = $alias;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function __invoke(string $name, ?string $alias = null): Column
    {
        return new Column($name, $this, $alias);
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        return new SqlQueryValue($this->table);
    }
}
