<?php

namespace Zimzat\QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Zimzat\QueryBuilder\Select;
use Zimzat\QueryBuilder\Union;

class UnionTest extends TestCase
{
    use HelperTrait;

    public function testBasic(): void
    {
        $union = new Union();

        $union->unionAll(new Select('SomeTable'));
        $union->unionAll(new Select('OtherTable'));

        self::assertOutputEquals(
            $union,
            <<<SQL
                (SELECT * FROM SomeTable)
                UNION ALL
                (SELECT * FROM OtherTable)
                SQL,
            [],
        );
    }

    public function testComplex(): void
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
            ->unionAll($select2);

        $union->limit(10);

        self::assertOutputEquals(
            $union,
            <<<SQL
                (SELECT TableA.id FROM TableA LIMIT ?)
                UNION ALL
                (SELECT TableB.id FROM TableB LIMIT ?)
                LIMIT ?
                SQL,
            [5, 5, 10],
        );
    }
}
