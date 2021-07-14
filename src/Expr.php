<?php

namespace Zimzat\QueryBuilder;

/**
 * Contains things like `MAX(?)`, ``POINT(?, ?)`
 * ? may be a parameter for binding a value or other Sql reference.
 */
class Expr implements Sql, Field
{
    protected string $expression;

    protected array $values;

    public function __construct(string $expression, mixed ...$values)
    {
        $this->expression = $expression;
        $this->values = $values;
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        return new SqlQueryValue($this->expression, $this->values);
    }
}
