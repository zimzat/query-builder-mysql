# PHP Query Builder for MySQL

A simple, flexible, and safe way to dynamically build queries for MySQL or similar databases.

## Features

- Parameterize inputs by default.
- Flexible to handle custom expressions not available out of the box.
- Uses object instances to maintain query state, no static methods.
- Works with any connection library (PDO or mysqli)

## Installation

### Composer
```bash
composer require zimzat/query-builder-mysql
```


## Usage

### Main Entry Points

```php
// new Select(TableReference|string $from);
// new Insert(Table|string $into, ?Select $select = null)
// new Update(Table|string $from)
// new Delete(Table|string $from)
```

### Output

The `SqlWriter` class can be used to generate the SQL string and Parameter array. It is light-weight and does not contain any state, making it safe to re-use or instantiate on demand.

```php
use Zimzat\QueryBuilder\Select;
use Zimzat\QueryBuilder\SqlWriter;

$writer = new SqlWriter();
[$sql, $parameters] = $writer->write(new Select('SomeTable'));

// [string $sql, array $parameters] = (new SqlWriter())->write(Sql $query)
```

### SELECT

```php
/** @see SelectTest */
/** @see ReadmeTest::testSelect() */

use Zimzat\QueryBuilder\Select;

$select = new Select('SomeTable');
// SELECT * FROM SomeTable
$select->columns()
    ->add($select('id'))
    ->add($select('*'));
// SELECT SomeTable.id, SomeTable.* FROM SomeTable

$otherTable = $select->join('OtherTable', 'someTableId', $select('id'));
// INNER JOIN OtherTable ON (OtherTable.someTableId = SomeTable.id)

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
```

`SELECT` queries can also be used as part of other statements as a Sub-Query or `INSERT ... SELECT`.

#### UNION

```php
/** @see UnionTest */
/** @see ReadmeTest::testUnion() */

use Zimzat\QueryBuilder\Select;
use Zimzat\QueryBuilder\Union;

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
// (SELECT TableA.id FROM TableA LIMIT ?)
// UNION ALL
// (SELECT TableB.id FROM TableB LIMIT ?)
// LIMIT ?
// [5, 5, 10]
```

#### Sub-Query

```php
/** @see SelectTest::testSubquery() */
/** @see ReadmeTest::testSubQuery() */

use Zimzat\QueryBuilder\Select;

$subselect = new Select('TableB');
$subselect->columns()
    ->add($subselect('id'));
$subselect->where()
    ->equal($subselect('f'), 'a');
$subselect->limit(10);

$select = new Select($subselect->asSubQuery('SubB'));
$tableF = $select->join('TableF', 'id', $select('id'));

// SELECT * FROM (SELECT TableB WHERE TableB.f = ? LIMIT 10) AS SubB JOIN TableF ON (TableF.id = SubB.id
```

### UPDATE

```php
/** @see UpdateTest */
/** @see ReadmeTest::testUpdate() */

use Zimzat\QueryBuilder\Update;

$update = new Update('TableU');
// UPDATE TableU

$update->set()
    ->equal($update('a'), 4);
// SET TableU.a = ?

// WHERE ()
$update->where()
    ->between($update('n'), 3, 5);
// TableU.n BETWEEN (?, ?)
```


### INSERT

The primary use-case for `Insert` is in conjunction with `Select`. It does not support multiple rows.

```php
/** @see InsertTest */
/** @see ReadmeTest::testDelete() */


```

### DELETE

```php
/** @see DeleteTest */
/** @see ReadmeTest::testDelete() */

use Zimzat\QueryBuilder\Delete;

$delete = new Delete('SomeTable');
$delete->where()
    ->expr('? = FLOOR(?)', $delete('f'), M_PI);
// DELETE SomeTable FROM SomeTable WHERE SomeTable.f = FLOOR(?)
// [3.141592653589793]
```

### Extensions

Any class implementing the `Sql` interface can be used in several places.

```php
use Zimzat\QueryBuilder\Sql;
use Zimzat\QueryBuilder\SqlQueryValue;
use Zimzat\QueryBuilder\SqlWriter;

// Normally this would be a standard class declaration, for demonstration purposes this uses `new class` instead
$x = new class implements Sql {
    public function compileSqlQueryValue() : SqlQueryValue
    {
        return new SqlQueryValue('CUSTOM QUERY PART WITH ? PLACEHOLDER', ['123']);
    }
};

(new SqlWriter())->write($x);
// ['CUSTOM QUERY PART WITH ? PLACEHOLDER', ['123']]
```

Extensions of the `Condition` or `Expr` class can be created to handle repeat expressions.

```php
/** @see ReadmeTest::testExtendCondition */

use Zimzat\QueryBuilder\Condition;
use Zimzat\QueryBuilder\Field;
use Zimzat\QueryBuilder\Select;

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

$select->where()
    ->equal($select('type'), 99)
    ->add($equalOrNull);
// WHERE (
//     SomeTable.type = ?
//     AND (SomeTable.x IS NULL OR SomeTable.x = ?)
// )
```

Alternatively, you can create your own wrapper to return a new stance of `Condition` to the same effect:

```php
use Zimzat\QueryBuilder\Condition;
use Zimzat\QueryBuilder\Field;

public function equalOrNull(Field $field, mixed $value): Condition
{
    return new Condition('(? IS NULL OR ? = ?)', $field, $field, $value);
}
```

## Library Comparisons

See: [Query Builder Library Comparison](Comparison.md)
