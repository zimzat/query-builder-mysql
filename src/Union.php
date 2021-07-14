<?php

namespace Zimzat\QueryBuilder;

class Union implements Sql
{
    protected const
        TYPE_DISTINCT = 'DISTINCT',
        TYPE_ALL = 'ALL';

    protected array $selects = []; // ['type' => distinct|all, 'select' => $select];

    protected OrderFieldList $orderBy;

    protected Limit $limit;

    public function __construct(Select $select = null)
    {
        if ($select) {
            $this->union($select);
        }

        $this->orderBy = new OrderFieldList();
        $this->limit = new Limit();
    }

    public function union(Select $select): self
    {
        $this->selects[] = ['type' => self::TYPE_DISTINCT, 'select' => $select];
        return $this;
    }

    public function unionAll(Select $select): self
    {
        $this->selects[] = ['type' => self::TYPE_ALL, 'select' => $select];
        return $this;
    }

    public function orderBy(): OrderFieldList
    {
        return $this->orderBy;
    }

    public function limit(?int $count = null, ?int $offset = null): Limit
    {
        if (func_num_args()) {
            $this->limit->set($count, $offset);
        }

        return $this->limit;
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        $query = [];
        $values = array_column($this->selects, 'select');

        $first = true;
        foreach ($this->selects as $info) {
            if ($first) {
                $first = false;
                $query[] = '(?)';
            } else {
                $query[] = ' UNION ' . $info['type'] . ' (?)';
            }
        }

        if ($this->orderBy->all()) {
            $query[] = 'ORDER BY ?';
            $values[] = $this->orderBy;
        }

        if ($this->limit->isSet()) {
            $query[] = '?';
            $values[] = $this->limit;
        }

        return new SqlQueryValue(
            implode('', $query),
            $values,
        );
    }
}
