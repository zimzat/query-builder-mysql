<?php

namespace Zimzat\QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Zimzat\QueryBuilder\Select;
use Zimzat\QueryBuilder\Update;

class UpdateTest extends TestCase
{
    use HelperTrait;

    public function testBasic(): void
    {
        $update = new Update('SomeTable');
        $update->set()
            ->equal($update('someField'), 2);

        self::assertOutputEquals(
            $update,
            <<<SQL
                UPDATE SomeTable
                SET SomeTable.someField = ?
                SQL,
            [2],
        );
    }

    public function testComplex(): void
    {
        $update = new Update('SomeTable');

        $update->options()->lowPriority();

        $update->set()
            ->equal($update('b'), $update('b'))
            ->equal($update('f'), 123456)
            ->expr('? = IFNULL(?, ?)', $update('y'), $update('b'), 'xyz');

        $update->join('SomeOtherTable', 'id', $update('id'));

        $exists = new Select('ThirdTable');
        $exists->columns()->expr('1');
        $exists->where()
            ->equal($exists('a'), 1);

        $update->where()
            ->notExists($exists);

        self::assertOutputEquals(
            $update,
            <<<SQL
                UPDATE LOW_PRIORITY SomeTable
                INNER JOIN SomeOtherTable ON (SomeOtherTable.id = SomeTable.id)
                SET SomeTable.b = SomeTable.b,
                    SomeTable.f = ?,
                    SomeTable.y = IFNULL(SomeTable.b, ?)
                WHERE (NOT EXISTS (SELECT 1 FROM ThirdTable WHERE (ThirdTable.a = ?)))
                SQL,
            [123456, 'xyz', 1],
        );
    }
}
