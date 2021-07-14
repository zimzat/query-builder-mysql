<?php

namespace Zimzat\QueryBuilder;

interface TableReference extends Sql
{
    public function getAlias(): ?string;

    public function __invoke(string $name, string $alias = null): Column;
}
