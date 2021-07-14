<?php

namespace Zimzat\QueryBuilder\Tests;

use Zimzat\QueryBuilder\Sql;
use Zimzat\QueryBuilder\SqlWriter;

trait HelperTrait
{
    public static function assertOutputEquals(Sql $input, string $sql, array $parameters): void
    {
        $output = (new SqlWriter())->write($input);

        self::assertEquals(
            self::splitForComparison($sql),
            self::splitForComparison($output[0]),
        );
        self::assertEquals($parameters, $output[1]);
    }

    private static function splitForComparison(string $string): string
    {
        $split = preg_split('#\s+|(?<=[()])|(?=[()])#', $string, flags: PREG_SPLIT_NO_EMPTY);
        if ($split === false) {
            throw new \InvalidArgumentException(preg_last_error_msg(), preg_last_error());
        }
        return implode("\n", $split);
    }
}
