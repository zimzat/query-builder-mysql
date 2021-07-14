<?php

namespace Zimzat\QueryBuilder;

interface Sql
{
    public function compileSqlQueryValue(): SqlQueryValue;
}
