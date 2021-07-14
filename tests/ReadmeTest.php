<?php

namespace Zimzat\QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Zimzat\QueryBuilder\Condition;
use Zimzat\QueryBuilder\Delete;
use Zimzat\QueryBuilder\Field;
use Zimzat\QueryBuilder\Insert;
use Zimzat\QueryBuilder\Select;
use Zimzat\QueryBuilder\Union;
use Zimzat\QueryBuilder\Update;

class ReadmeTest extends TestCase
{
    use HelperTrait;

    public function testSelect(): void
    {
        $select = new Select('SomeTable');
        // SELECT * FROM SomeTable
        $select->columns()
            ->add($select('id'))
            ->add($select('*'));
        // SELECT SomeTable.id, SomeTable.* FROM SomeTable

        $otherTable = $select->join('OtherTable', 'someTableId', $select('id'));
        // JOIN OtherTable ON (OtherTable.someTableId = SomeTable.id)

        $thirdTable = $select->leftJoin('ThirdTable', 'id', $select('thirdTableId'));
        $thirdTable->on()
            ->notEqual($thirdTable('field1'), $select('field2'));
        // LEFT JOIN ThirdTable ON (ThirdTable.id = SomeTable.thirdTableId AND ThirdTable.field1 != SomeTable.field2)

        // WHERE ()
        $select->where()
            ->equal($select('someField'), 4)
            ->in($select('type'), ['a', 'b']);
        // SomeTable.someField = ? AND SomeTable.type IN (?, ?)

        $select->where()
            ->some()
                ->isNull($otherTable('nullableValue'))
                ->lessThanOrEqual($otherTable('nullableValue'), 10);
        // AND (OtherTable.nullableValue IS NULL OR OtherTable.nullableValue <= ?)

        $select->where()
            ->condition('? <=> ?', $select('f'), $otherTable('y'));
        // AND (SomeTable.f <=> OtherTable.y)

        // GROUP BY
        $select->groupBy()
            ->add($select('id'));
        // SomeTable.id ASC

        $select->limit(10);
        // LIMIT ?

        self::assertOutputEquals(
            $select,
            <<<SQL
                SELECT SomeTable.id, SomeTable.*
                FROM SomeTable
                INNER JOIN OtherTable ON (OtherTable.someTableId = SomeTable.id)
                LEFT JOIN ThirdTable ON (
                    ThirdTable.id = SomeTable.thirdTableId
                    AND ThirdTable.field1 != SomeTable.field2
                )
                WHERE (
                    SomeTable.someField = ?
                    AND SomeTable.type IN (?, ?)
                    AND (OtherTable.nullableValue IS NULL OR OtherTable.nullableValue <= ?)
                    AND SomeTable.f <=> OtherTable.y
                )
                GROUP BY SomeTable.id ASC
                LIMIT ?
                SQL,
            [4, 'a', 'b', 10, 10],
        );
    }

    public function testUnion(): void
    {
        $select1 = new Select('TableA');
        $select1->columns()->add($select1('id'));
        $select1->limit(5);
        // SELECT TableA.id FROM TableA LIMIT ?
        // [5]

        $select2 = new Select('TableB');
        $select2->columns()->add($select2('id'));
        $select2->limit(5);
        // SELECT TableB.id FROM TableB LIMIT ?
        // [5]

        $union = new Union();
        $union
            ->unionAll($select1)
            ->unionAll($select2)
            ->limit(10);

        self::assertOutputEquals(
            $union,
            <<<SQL
                (SELECT TableA.id FROM TableA LIMIT ?)
                UNION ALL
                (SELECT TableB.id FROM TableB LIMIT ?)
                LIMIT ?
                SQL,
            [5, 5, 10]
        );
    }

    public function testSubQuery(): void
    {
        $subselect = new Select('TableB');
        $subselect->columns()
            ->add($subselect('id'));
        $subselect->where()
            ->equal($subselect('f'), 'a');
        $subselect->limit(10);

        $select = new Select($subselect->asSubQuery('SubB'));
        $tableF = $select->join('TableF', 'id', $select('id'));

        self::assertOutputEquals(
            $select,
            <<<SQL
                SELECT *
                FROM (
                    SELECT TableB.id
                    FROM TableB
                    WHERE (TableB.f = ?)
                    LIMIT ?
                ) AS SubB
                INNER JOIN TableF ON (TableF.id = SubB.id)
                SQL,
            ['a', 10]
        );
    }

    public function testUpdate(): void
    {
        $update = new Update('TableU');
        // UPDATE TableU

        $update->set()
            ->equal($update('a'), 4);
        // SET TableU.a = ?

        // WHERE ()
        $update->where()
            ->between($update('n'), 3, 5);

        self::assertOutputEquals(
            $update,
            <<<SQL
                UPDATE TableU
                SET TableU.a = ?
                WHERE (TableU.n BETWEEN ? AND ?)
                SQL,
            [4, 3, 5],
        );
    }

    public function testInsert(): void
    {
        $insert = new Insert('SomeTable');
        $insert->set()
            ->equal($insert('x'), 1)
            ->equal($insert('y'), '1')
            ->equal($insert('z'), $z = new class implements \Stringable {
                public function __toString(): string
                {
                    return 'magic';
                }
            });

        self::assertOutputEquals(
            $insert,
            <<<SQL
                INSERT INTO SomeTable
                SET SomeTable.x = ?,
                    SomeTable.y = ?,
                    SomeTable.z = ?
                SQL,
            [1, '1', $z],
        );
    }

    public function testDelete(): void
    {
        $delete = new Delete('SomeTable');
        $delete->where()
            ->expr('? = FLOOR(?)', $delete('f'), M_PI);

        self::assertOutputEquals(
            $delete,
            <<<SQL
                DELETE SomeTable
                FROM SomeTable
                WHERE (SomeTable.f = FLOOR(?))
                SQL,
            [M_PI]
        );
    }

    public function testExtendCondition(): void
    {
        $select = new Select('SomeTable');

        // Normally this would be a standard class declaration, for demonstration purposes this uses `new class` instead
        $equalOrNull = new class ($select('x'), 5) extends Condition {
            public function __construct(Field $field, mixed $value)
            {
                parent::__construct('(? IS NULL OR ? = ?)', $field, $field, $value);
            }
        };

        $select->where()
            ->equal($select('type'), 99)
            ->add($equalOrNull);

        self::assertOutputEquals(
            $select,
            <<<SQL
                SELECT *
                FROM SomeTable
                WHERE (
                    SomeTable.type = ?
                    AND (SomeTable.x IS NULL OR SomeTable.x = ?)
                )
                SQL,
            [99, 5],
        );
    }
}
