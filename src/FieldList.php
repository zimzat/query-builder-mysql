<?php

namespace Zimzat\QueryBuilder;

class FieldList implements Sql, \IteratorAggregate
{
    use ListTrait;

    /** @var Sql[]|Field[] */
    protected array $items = [];

    public const
        OUTPUT_SOURCE = 1,
        OUTPUT_ALIAS = 2,
        OUTPUT_AS_ALIAS = 3;

    protected int $outputAs = self::OUTPUT_SOURCE;

    /** @return $this */
    public function setOutputAs(int $outputAs): self
    {
        $this->outputAs = $outputAs;
        return $this;
    }

    /** @return Sql[]|Field[] */
    public function all(): array
    {
        return $this->items;
    }

    /** @return $this */
    public function add(Sql|Field $field): self
    {
        $this->items[] = $field;
        return $this;
    }

    public function expr(string $expression, mixed ...$values): self
    {
        return $this->add(new Expr($expression, ...$values));
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        if ($this->outputAs === self::OUTPUT_AS_ALIAS) {
            return new SqlQueryValue(
                '??' . str_repeat(', ??', count($this->items) - 1),
                array_merge(...array_map( // "zipper" / alternate array item merge
                    null,
                    $this->items,
                    array_map(
                        static fn (Field $field) => $field instanceof Column && $field->getAlias()
                            ? new Expr(' AS ' . $field->getAlias())
                            : new Expr(''),
                        $this->items,
                    ),
                )),
            );
        } elseif ($this->outputAs === self::OUTPUT_ALIAS) {
            return new SqlQueryValue(
                '?' . str_repeat(', ?', count($this->items) - 1),
                array_map(
                    static fn (Field $field) => $field instanceof Column && $field->getAlias() !== null
                        ? new Expr($field->getAlias())
                        : $field,
                    $this->items,
                ),
            );
        }

        return new SqlQueryValue(
            '?' . str_repeat(', ?', count($this->items) - 1),
            $this->items,
        );
    }
}
