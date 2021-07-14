<?php

namespace Zimzat\QueryBuilder;

/*
https://dev.mysql.com/doc/refman/5.7/en/select.html
https://dev.mysql.com/doc/refman/5.7/en/join.html

SELECT
    - DISTINCT
    - HIGH_PRIORITY
    - STRAIGHT_JOIN
    - SQL_SMALL_RESULT
    - SQL_BIG_RESULT
    - SQL_BUFFER_RESULT
    - SQL_NO_CACHE
    - SQL_CALC_FOUND_ROWS
- [select_expr]
- FROM [table_references]
- WHERE [where_condition]
- GROUP BY [col_name | expr | position]
    - WITH ROLLUP
- HAVING [where_condition]
- ORDER BY [col_name | expr | position]
    - WITH ROLLUP
- LIMIT [row_count, offset]
- into_option

[select_expr] = [col_name | expr]
[where_condition] = [expr]
*/

class Select implements Sql
{
    use JoinTrait;

    protected TableReference $from;

    protected SelectOptionList $options;

    protected FieldList $columns;

    protected JoinList $joins;

    protected ConditionList $where;

    protected OrderFieldList $groupBy;

    protected ConditionList $having;

    protected OrderFieldList $orderBy;

    protected Limit $limit;

    public function __construct(TableReference|string $from)
    {
        $this->from = is_string($from) ? new Table($from) : $from;

        $this->options = (new SelectOptionList());
        $this->columns = (new FieldList());
        $this->joins = (new JoinList());
        $this->where = (new ConditionList());
        $this->groupBy = (new OrderFieldList());
        $this->having = (new ConditionList());
        $this->orderBy = (new OrderFieldList());
        $this->limit = (new Limit());
    }

    public function asSubQuery(string $alias): SubQuery
    {
        return new SubQuery($this, $alias);
    }

    public function __invoke(string $name, string $alias = null): Column
    {
        return new Column($name, $this->from, $alias);
    }

    public function options(): SelectOptionList
    {
        return $this->options;
    }

    public function columns(): FieldList
    {
        return $this->columns;
    }

    public function from(): TableReference
    {
        return $this->from;
    }

    public function joins(): JoinList
    {
        return $this->joins;
    }

    public function where(): ConditionList
    {
        return $this->where;
    }

    public function groupBy(): OrderFieldList
    {
        return $this->groupBy;
    }

    public function having(): ConditionList
    {
        return $this->having;
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

    public function getTableByName(string $table): ?TableReference
    {
        if ($this->from->getAlias() === $table) {
            return $this->from;
        }

        foreach ($this->joins as $join) {
            $tableReference = $join->getTableReference();
            if ($tableReference instanceof Table && $tableReference->getTable() === $table) {
                return $join;
            }
        }

        return null;
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        $query = ['SELECT'];
        $values = [];

        if ($this->options->all()) {
            $query[] = '?';
            $values[] = $this->options;
        }

        if ($this->columns->all()) {
            $query[] = '?';
            $values[] = $this->columns->setOutputAs(FieldList::OUTPUT_AS_ALIAS);
        } else {
            $query[] = '*';
        }

        $query[] = 'FROM';
        if ($this->from->getAlias()) {
            $query[] = '? AS ' . $this->from->getAlias();
        } else {
            $query[] = '?';
        }
        $values[] = $this->from;

        if ($this->joins->all()) {
            $query[] = '?';
            $values[] = $this->joins;
        }

        if ($this->where->all()) {
            $query[] = 'WHERE ?';
            $values[] = $this->where;
        }

        if ($this->groupBy->all()) {
            $query[] = 'GROUP BY ?';
            $values[] = $this->groupBy;
        }

        if ($this->having->all()) {
            $query[] = 'HAVING ?';
            $values[] = $this->having;
        }

        if ($this->orderBy->all()) {
            $query[] = 'ORDER BY ?';
            $values[] = $this->orderBy;
        }

        if ($this->limit->isSet()) {
            $query[] = '?';
            $values[] = $this->limit;
        }

        return new SqlQueryValue(implode("\n", $query), $values);
    }
}
