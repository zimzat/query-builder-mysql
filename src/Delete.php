<?php

namespace Zimzat\QueryBuilder;

/*
https://dev.mysql.com/doc/refman/5.7/en/delete.html
https://dev.mysql.com/doc/refman/5.7/en/join.html

DELETE [LOW_PRIORITY] [QUICK] [IGNORE] FROM tbl_name
    [PARTITION (partition_name [, partition_name] ...)]
    [WHERE where_condition]
    [ORDER BY ...]
    [LIMIT row_count]

DELETE [LOW_PRIORITY] [QUICK] [IGNORE]
    tbl_name[.*] [, tbl_name[.*]] ...
    FROM table_references
    [WHERE where_condition]

DELETE [LOW_PRIORITY] [QUICK] [IGNORE]
    FROM tbl_name[.*] [, tbl_name[.*]] ...
    USING table_references
    [WHERE where_condition]
*/

class Delete implements Sql
{
    use JoinTrait;

    protected Table $from;

    protected TableList $tables;

    protected DeleteOptionList $options;

    protected JoinList $joins;

    protected ConditionList $where;

    protected OrderFieldList $orderBy;

    protected Limit $limit;

    public function __construct(Table|string $from)
    {
        $this->from = is_string($from) ? new Table($from) : $from;
        $this->tables = (new TableList())->add($this->from);

        $this->options = (new DeleteOptionList());
        $this->joins = (new JoinList());
        $this->where = (new ConditionList());
        $this->orderBy = (new OrderFieldList());
        $this->limit = (new Limit());
    }

    public function __invoke(string $name): Column
    {
        return new Column($name, $this->from);
    }

    public function options(): DeleteOptionList
    {
        return $this->options;
    }

    public function tables(): TableList
    {
        return $this->tables;
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
        $query = ['DELETE'];

        if ($this->options->all()) {
            $query[] = '?';
            $values[] = $this->options;
        }

        $query[] = '?';
        $values[] = $this->tables;

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
