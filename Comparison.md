
# Library Comparison

## [Nil Portugués SQL Query Builder](https://github.com/nilportugues/php-sql-query-builder)

The Nil Portugués library covers many of the same scenarios this library does, in very similar ways, does so for more types of databases, and includes an optional formatter for easier debugging. This was previously my go-to library and one I would still generally recommend.

The only real downside to this library is the lack of maintenance, and the lack of flexibility to handle new scenarios.
- [Comparison against empty string converted to 'NULL' string](https://github.com/nilportugues/php-sql-query-builder/issues/106)

And the hard-to-remember handling of join statement columns:

```php
->leftJoin(
    'news', //join table
    'user_id', //origin table field used to join
    'author_id', //join column
)
```

-----

## [Doctrine DBAL Query Builder](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/query-builder.html)

### Architecture

Doctrine's Query Builder architecture is built primarily around a single QueryBuilder class that is intended to handle all structure and relations. It is up to the developer to write the necessary conditionals, remember where to put placeholders, and keep track of the parts of the query built so far.  This makes it much more difficult to build a modular query builder based on specific sets of filters.

### Binding Parameters to Placeholders

Doctrine requires special care when handling input values to ensure accidental SQL injection does not occur. This syntax is verbose and easy to forget.

```php
$queryBuilder
    ->select('id', 'name')
    ->from('users')
    ->where('email = ?')
    ->setParameter(0, $userInputEmail)
;

$queryBuilder
    ->select('id', 'name')
    ->from('users')
    ->where('email = ' .  $queryBuilder->createPositionalParameter($userInputEmail))
;
```

### WHERE syntax

Doctrine's `where()` syntax is awkward to work with dynamically, as it requires always knowing if something has already added a WHERE statement.

> Calling `where()` overwrites the previous clause and you can prevent this by combining expressions with `andWhere()` and `orWhere()` methods.

It doesn't make sense to allow mixing and matching `AND` and `OR` statements at the same level; having one section of code do `orWhere` and another do `andWhere` is likely to result in unexpected results.
