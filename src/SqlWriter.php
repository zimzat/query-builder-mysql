<?php

namespace Zimzat\QueryBuilder;

class SqlWriter
{
    /**
     * @return array{string, array<string|int|float|bool>}
     */
    public function write(Sql $sql): array
    {
        [$query, $values] = $sql->compileSqlQueryValue()->getQueryValues();
        if (!$values) {
            return [$query, $values];
        }

        $postSql = '';
        $postValues = [];
        $parts = explode('?', $query);
        assert(count($parts) === count($values) + 1, 'Passed in a different number of parameters and placeholders');
        foreach ($parts as $i => $part) {
            $postSql .= $part;
            if (!array_key_exists($i, $values)) {
                continue;
            }

            if ($values[$i] instanceof Sql) {
                [$innerSql, $innerValues] = $this->write($values[$i]);
//                if ($values[$i] instanceof Select) {
//                    $postSql .= '(' . $innerSql . ')';
//                } else {
                    $postSql .= $innerSql;
//                }
                array_push($postValues, ...$innerValues);
            } elseif (is_iterable($values[$i])) {
                $postSqlParts = [];
                foreach ($values[$i] as $indexValue) {
                    if ($indexValue instanceof Sql) {
                        [$innerSql, $innerValues] = $this->write($indexValue);
//                        if ($indexValue instanceof Select) {
//                            $postSqlParts[] = '(' . $innerSql . ')';
//                        } else {
                            $postSqlParts[] = $innerSql;
//                        }
                        array_push($postValues, ...$innerValues);
                    } else {
                        $postSqlParts[] = '?';
                        $postValues[] = $indexValue;
                    }
                }

                $postSql .= implode(', ', $postSqlParts);
            } else {
                $postSql .= '?';
                $postValues[] = $values[$i];
            }
        }

        return [$postSql, $postValues];
    }
}
