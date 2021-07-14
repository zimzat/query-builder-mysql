<?php

namespace Zimzat\QueryBuilder;

trait JoinTrait
{
    protected JoinList $joins;

    public function join(string $table, string $column = null, Column $equal = null, string $alias = null): Join
    {
        $this->joins->add($join = new Join(new Table($table, $alias), Join::TYPE_INNER_JOIN));
        if ($column && $equal) {
            $join->on()->equal($join($column), $equal);
        }
        return $join;
    }

    public function leftJoin(string $table, string $column = null, Column $equal = null, string $alias = null): Join
    {
        $this->joins->add($join = new Join(new Table($table, $alias), Join::TYPE_LEFT_JOIN));
        if ($column && $equal) {
            $join->on()->equal($join($column), $equal);
        }
        return $join;
    }

    public function rightJoin(string $table, string $alias = null): Join
    {
        $this->joins->add($join = new Join(new Table($table, $alias), Join::TYPE_RIGHT_JOIN));
        return $join;
    }

    public function straightJoin(string $table, string $alias = null): Join
    {
        $this->joins->add($join = new Join(new Table($table, $alias), Join::TYPE_STRAIGHT_JOIN));
        return $join;
    }
}
