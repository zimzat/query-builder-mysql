<?php

namespace Zimzat\QueryBuilder;

class JoinList implements Sql, \IteratorAggregate
{
    use ListTrait;

    /** @var Join[] */
    protected array $items = [];

    public function all(): array
    {
        return $this->items;
    }

    public function add(Join $join): self
    {
        $this->items[] = $join;
        return $this;
    }

    public function getByAlias(string $alias): ?TableReference
    {
        foreach ($this->items as $join) {
            if ($join->getAlias() === $alias) {
                return $join;
            }
        }

        return null;
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        return new SqlQueryValue(
            '?' . str_repeat("\n?", count($this->items) - 1),
            $this->items,
        );
    }
}
