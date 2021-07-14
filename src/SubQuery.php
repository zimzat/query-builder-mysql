<?php

namespace Zimzat\QueryBuilder;

class SubQuery implements Sql, TableReference
{
    public function __construct(
        protected Select $select,
        protected string $alias,
    ) {
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        [$sql, $parameters] = $this->select->compileSqlQueryValue()->getQueryValues();
        return new SqlQueryValue('(' . $sql . ')', $parameters);
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function __invoke(string $name, string $alias = null): Column
    {
        return new Column($name, $this, $alias);
    }
}
