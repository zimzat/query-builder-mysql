<?php

namespace Zimzat\QueryBuilder;

class AssignmentList implements Sql, \IteratorAggregate
{
    use ListTrait;

    /** @var Sql[] */
    protected array $items = [];

    public function all(): array
    {
        return $this->items;
    }

    public function add(Sql $field): self
    {
        $this->items[] = $field;
        return $this;
    }

    public function equal(Field $a, mixed $b): self
    {
        return $this->add(new Condition('? = ?', $a, $b));
    }

    public function default(Field $a): self
    {
        return $this->add(new Condition('? = DEFAULT', $a));
    }

    public function values(Field $a, Field $b): self
    {
        return $this->add(new Condition('? = VALUES(?)', $a, $b));
    }

    public function expr(string $expression, mixed ...$values): self
    {
        return $this->add(new Expr($expression, ...$values));
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        return new SqlQueryValue(
            '?' . str_repeat(', ?', count($this->items) - 1),
            $this->items,
        );
    }
}
