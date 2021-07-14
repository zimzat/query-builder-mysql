<?php

namespace Zimzat\QueryBuilder;

/*
https://dev.mysql.com/doc/refman/5.7/en/insert.html
https://dev.mysql.com/doc/refman/5.7/en/insert-on-duplicate.html

INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
    [INTO] tbl_name
    [PARTITION (partition_name [, partition_name] ...)]
    [(col_name [, col_name] ...)]
    {VALUES | VALUE} (value_list) [, (value_list)] ...
    [ON DUPLICATE KEY UPDATE assignment_list]

INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
    [INTO] tbl_name
    [PARTITION (partition_name [, partition_name] ...)]
    SET assignment_list
    [ON DUPLICATE KEY UPDATE assignment_list]

INSERT [LOW_PRIORITY | HIGH_PRIORITY] [IGNORE]
    [INTO] tbl_name
    [PARTITION (partition_name [, partition_name] ...)]
    [(col_name [, col_name] ...)]
    SELECT ...
    [ON DUPLICATE KEY UPDATE assignment_list]

value:
    {expr | DEFAULT}

value_list:
    value [, value] ...

assignment:
    col_name = value

assignment_list:
    assignment [, assignment] ...
*/

class Insert implements Sql
{
    protected Table $into;

    protected InsertOptionList $options;

    protected AssignmentList $set;

    protected ?Select $select = null;

    protected AssignmentList $onDuplicateKeyUpdate;

    public function __construct(Table|string $into, ?Select $select = null)
    {
        $this->into = is_string($into) ? new Table($into) : $into;

        $this->options = (new InsertOptionList());
        $this->set = (new AssignmentList());
        $this->select = $select;
        $this->onDuplicateKeyUpdate = (new AssignmentList());
    }

    public function __invoke(string $name): Column
    {
        return new Column($name, $this->into);
    }

    public function options(): InsertOptionList
    {
        return $this->options;
    }

    public function into(): Table
    {
        return $this->into;
    }

    public function set(): AssignmentList
    {
        return $this->set;
    }

    public function onDuplicateKeyUpdate(): AssignmentList
    {
        return $this->onDuplicateKeyUpdate;
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        $query = ['INSERT'];

        if ($this->options->all()) {
            $query[] = '?';
            $values[] = $this->options;
        }

        $query[] = 'INTO ?';
        $values[] = $this->into;

        if ($this->select) {
            $query[] = '?';
            $values[] = $this->select;
        } else {
            $query[] = 'SET ?';
            $values[] = $this->set;
        }

        if ($this->onDuplicateKeyUpdate->all()) {
            $query[] = 'ON DUPLICATE KEY UPDATE ?';
            $values[] = $this->onDuplicateKeyUpdate;
        }

        return new SqlQueryValue(implode("\n", $query), $values);
    }
}
