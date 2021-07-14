<?php

namespace Zimzat\QueryBuilder;

/**
 * Contains things like `? = ?`, `? IN (?)`
 * ? may be a parameter for binding a value, column, or other Sql reference.
 */
class Condition implements Sql
{
    protected string $condition;

    protected array $values;

    public function __construct(string $condition, mixed ...$values)
    {
        $this->condition = $condition;
        $this->values = $values;
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        return new SqlQueryValue($this->condition, $this->values);
    }
}
