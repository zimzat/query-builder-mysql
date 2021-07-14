<?php

namespace Zimzat\QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Zimzat\QueryBuilder\Select;

class SelectTest extends TestCase
{
    use HelperTrait;

    public function testBasic(): void
    {
        $select = new Select('SomeTable');

        self::assertOutputEquals(
            $select,
            <<<SQL
                SELECT *
                FROM SomeTable
                SQL,
            [],
        );
    }

    public function testComplex(): void
    {
        $select = new Select('SomeTable');

        $select->options()->distinct();

        $select->columns()
            ->add($select('*'));

        $otherTable = $select->join('OtherTable', 'someTableId', $select('id'));

        $select->where()
            ->isNotNull($otherTable('arbitraryField'))
            ->some()
                ->isNull($select('nullableField'))
                ->every()
                    ->isNotNull($select('nullableField'))
                    ->equal($otherTable('valueField'), 4)
                ->end()
                ->some()
                    ->notIn($otherTable('valueField'), [3, 'test', 9])
                    ->like($otherTable('stringField'), 'Z%');

        $select->groupBy()
            ->add($select('id'));

        $select->having()
            ->expr('COUNT(DISTINCT ?) = ?', $otherTable('valueField'), 1);

        $select->orderBy()
            ->add($select('id'));

        $select->limit()->set(20);

        self::assertOutputEquals(
            $select,
            <<<SQL
                SELECT DISTINCT
                    SomeTable.*
                FROM SomeTable
                    INNER JOIN OtherTable ON (OtherTable.someTableId = SomeTable.id)
                WHERE (
                    OtherTable.arbitraryField IS NOT NULL 
                    AND (
                        SomeTable.nullableField IS NULL 
                        OR (
                            SomeTable.nullableField IS NOT NULL 
                            AND OtherTable.valueField = ?
                        ) 
                        OR (
                            OtherTable.valueField NOT IN (?, ?, ?) 
                            OR OtherTable.stringField LIKE ?
                        )
                    )
                )
                GROUP BY SomeTable.id ASC
                HAVING (
                    COUNT(DISTINCT OtherTable.valueField) = ?
                )
                ORDER BY SomeTable.id ASC
                LIMIT ?
                SQL,
            [4, 3, 'test', 9, 'Z%', 1, 20],
        );
    }

    public function testLimitDirect(): void
    {
        $select = new Select('SomeTable');
        $select->limit(20);

        self::assertOutputEquals(
            $select,
            <<<SQL
                SELECT *
                FROM SomeTable
                LIMIT ?
                SQL,
            [20],
        );
    }

    public function testSubquery(): void
    {
        $selectSub = new Select('TableB');
        $selectSub->columns()
            ->add($selectSub('id'));
        $selectSub->where()
            ->equal($selectSub('f'), 'a');
        $selectSub->limit()->set(10);

        $select = new Select($selectSub->asSubQuery('SubB'));
        $tableF = $select->join('TableF', 'id', $select('id'));

        $select->where()
            ->greaterThan($tableF('id'), 99);

        $select->limit(20);

        self::assertOutputEquals(
            $select,
            <<<SQL
                SELECT *
                FROM (
                        SELECT
                            TableB.id
                        FROM TableB
                        WHERE (TableB.f = ?)
                        LIMIT ?
                    ) AS SubB
                    INNER JOIN TableF ON (TableF.id = SubB.id)
                WHERE (TableF.id > ?)
                LIMIT ?
                SQL,
            ['a', 10, 99, 20],
        );
    }

    public function testHasAny(): void
    {
        $select = new Select('SomeTable');
        $select->columns()
            ->expr('1');

        $select->where()
            ->between($select('f'), $select('a'), $select('b'));

        $select->orderBy()
            ->add($select('id'));

        $select->limit(1);

        self::assertOutputEquals(
            $select,
            <<<SQL
                SELECT 1
                FROM SomeTable
                WHERE (SomeTable.f BETWEEN SomeTable.a AND SomeTable.b)
                ORDER BY SomeTable.id ASC
                LIMIT ?
                SQL,
            [1],
        );
    }
}
