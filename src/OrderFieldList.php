<?php

namespace Zimzat\QueryBuilder;

class OrderFieldList extends FieldList
{
    protected array $directions = [];

    /** @param Field $field */
    public function add(Sql|Field $field, string $direction = 'ASC'): self
    {
        $this->directions[] = $direction;
        return parent::add($field);
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        return new SqlQueryValue(
            '? ?' . str_repeat(', ? ?', count($this->items) - 1),
            array_merge(...array_map( // "zipper" / alternate array item merge
                null,
                array_map(
                    static fn (Field $field) => $field instanceof Column && $field->getAlias() !== null
                        ? new Expr($field->getAlias())
                        : $field,
                    $this->items,
                ),
                array_map(static fn (string $direction) => new Expr($direction), $this->directions),
            )),
        );
    }
}
