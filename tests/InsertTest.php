<?php

namespace Zimzat\QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Zimzat\QueryBuilder\Insert;
use Zimzat\QueryBuilder\Select;

class InsertTest extends TestCase
{
    use HelperTrait;

    public function testBasic(): void
    {
        $insert = new Insert('SomeTable');
        $insert->set()
            ->equal($insert('someField'), 4);

        self::assertOutputEquals(
            $insert,
            <<<SQL
                INSERT INTO SomeTable
                SET SomeTable.someField = ?
                SQL,
            [4],
        );
    }

    public function testComplex(): void
    {
        $insert = new Insert('SomeTable');

        $insert->options()->highPriority();

        $insert->set()
            ->equal($insert('someField'), 4)
            ->expr('? = IFNULL(?, ?)', $insert('otherField'), $insert('otherField'), 939);

        self::assertOutputEquals(
            $insert,
            <<<SQL
                INSERT HIGH_PRIORITY INTO SomeTable
                SET SomeTable.someField = ?,
                    SomeTable.otherField = IFNULL(SomeTable.otherField, ?)
                SQL,
            [4, 939],
        );
    }

    public function testSelect(): void
    {
        $select = new Select('SomeOtherTable');
        $select->where()
            ->notLike($select('a'), '%help%');
        $select->limit(77);

        $insert = new Insert('SomeTable', $select);

        self::assertOutputEquals(
            $insert,
            <<<SQL
                INSERT INTO SomeTable
                SELECT * FROM SomeOtherTable WHERE (SomeOtherTable.a NOT LIKE ?) LIMIT ?
                SQL,
            ['%help%', 77]
        );
    }
}
