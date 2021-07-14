<?php

namespace Zimzat\QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Zimzat\QueryBuilder\Delete;

class DeleteTest extends TestCase
{
    use HelperTrait;

    public function testBasic(): void
    {
        $delete = new Delete('SomeTable');

        self::assertOutputEquals(
            $delete,
            <<<SQL
                DELETE SomeTable
                FROM SomeTable
                SQL,
            [],
        );
    }

    public function testComplex(): void
    {
        $delete = new Delete('SomeTable');

        $delete->options()->ignore();

        $delete->where()
            ->like($delete('a'), 'Special%');
        $delete->limit(10);

        self::assertOutputEquals(
            $delete,
            <<<SQL
                DELETE IGNORE SomeTable
                FROM SomeTable
                WHERE (SomeTable.a LIKE ?)
                LIMIT ?
                SQL,
            ['Special%', 10],
        );
    }
}
