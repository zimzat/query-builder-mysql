<?php

namespace Zimzat\QueryBuilder;

class Join implements Sql, TableReference
{
    public const
        TYPE_INNER_JOIN = 'INNER JOIN',
        TYPE_LEFT_JOIN = 'LEFT JOIN',
        TYPE_RIGHT_JOIN = 'RIGHT JOIN',
        TYPE_STRAIGHT_JOIN = 'STRAIGHT_JOIN';

    protected TableReference $tableReference;

    protected string $type;

    protected ConditionList $on;

    protected ColumnList $using;

    public function __construct(TableReference $tableReference, string $type = self::TYPE_INNER_JOIN)
    {
        $this->tableReference = $tableReference;
        $this->type = $type;
        $this->on = new ConditionList();
        $this->using = new ColumnList();
    }

    public function __invoke(string $name, string $alias = null): Column
    {
        return new Column($name, $this->tableReference, $alias);
    }

    public function on(): ConditionList
    {
        return $this->on;
    }

    public function using(): ColumnList
    {
        return $this->using;
    }

    public function getTableReference(): TableReference
    {
        return $this->tableReference;
    }

    public function getAlias(): ?string
    {
        return $this->tableReference->getAlias();
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        $query = [$this->type];
        $values = [];

        if ($this->tableReference->getAlias()) {
            $query[] = '? AS ' . $this->tableReference->getAlias();
        } else {
            $query[] = '?';
        }
        $values[] = $this->tableReference;

        if ($this->on->all()) {
            $query[] = 'ON ?';
            $values[] = $this->on;
        } elseif ($this->using->all()) {
            $query[] = 'USING (?)';
            $values[] = $this->using;
        }

        return new SqlQueryValue(
            implode(' ', $query),
            $values,
        );
    }
}
