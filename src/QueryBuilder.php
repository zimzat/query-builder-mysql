<?php

namespace Zimzat\QueryBuilder;

/**
 * This class is intended primarily as a reference for the available top-level functionality.
 *
 * @internal
 */
class QueryBuilder
{
    public function select(TableReference|string $from): Select
    {
        return new Select($from);
    }

    public function union(): Union
    {
        return new Union();
    }

    public function insert(Table|string $into): Insert
    {
        return new Insert($into);
    }

    public function update(Table|string $from): Update
    {
        return new Update($from);
    }

    public function delete(Table|string $from): Delete
    {
        return new Delete($from);
    }

    /**
     * @return array{string, array<string|int|float|bool>}
     */
    public function build(Sql $sql): array
    {
        return (new SqlWriter())->write($sql);
    }
}
