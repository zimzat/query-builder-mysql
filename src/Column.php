<?php

namespace Zimzat\QueryBuilder;

class Column implements Sql, Field
{
    private string $name;

    private ?TableReference $table;

    private ?string $alias;

    public function __construct(string $name, ?TableReference $table = null, ?string $alias = null)
    {
        assert(preg_match('#^([0-9a-zA-Z$_]+|\*)$#', $name) === 1);
        assert($alias === null || preg_match('#^[0-9a-zA-Z$_]+$#', $alias) === 1);

        $this->name = $name;
        $this->table = $table;
        $this->alias = $alias;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        if ($this->table?->getAlias()) {
            return new SqlQueryValue($this->table->getAlias() . '.' . $this->name);
        }

        if ($this->table instanceof Table) {
            return new SqlQueryValue($this->table->getTable() . '.' . $this->name);
        }

        return new SqlQueryValue($this->name);
    }
}
