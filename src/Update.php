<?php

namespace Zimzat\QueryBuilder;

/*
https://dev.mysql.com/doc/refman/5.7/en/update.html
https://dev.mysql.com/doc/refman/5.7/en/join.html

UPDATE [LOW_PRIORITY] [IGNORE] table_reference
    SET assignment_list
    [WHERE where_condition]
    [ORDER BY ...]
    [LIMIT row_count]

value:
    {expr | DEFAULT}

assignment:
    col_name = value

assignment_list:
    assignment [, assignment] ...

[where_condition] = [expr]
*/

class Update implements Sql
{
    use JoinTrait;

    protected Table $from;

    protected UpdateOptionList $options;

    protected AssignmentList $set;

    protected JoinList $joins;

    protected ConditionList $where;

    protected OrderFieldList $orderBy;

    protected Limit $limit;

    public function __construct(Table|string $from)
    {
        $this->from = is_string($from) ? new Table($from) : $from;

        $this->options = (new UpdateOptionList());
        $this->joins = (new JoinList());
        $this->set = (new AssignmentList());
        $this->where = (new ConditionList());
        $this->orderBy = (new OrderFieldList());
        $this->limit = (new Limit());
    }

    public function __invoke(string $name): Column
    {
        return new Column($name, $this->from);
    }

    public function options(): UpdateOptionList
    {
        return $this->options;
    }

    public function from(): Table
    {
        return $this->from;
    }

    public function set(): AssignmentList
    {
        return $this->set;
    }

    public function joins(): JoinList
    {
        return $this->joins;
    }

    public function where(): ConditionList
    {
        return $this->where;
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
        $query = ['UPDATE'];

        if ($this->options->all()) {
            $query[] = '?';
            $values[] = $this->options;
        }

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

        $query[] = 'SET ?';
        $values[] = $this->set;

        if ($this->where->all()) {
            $query[] = 'WHERE ?';
            $values[] = $this->where;
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
