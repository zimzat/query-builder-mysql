<?php

namespace Zimzat\QueryBuilder;

class OptionList implements Sql, \IteratorAggregate
{
    use ListTrait;

    protected array $options = [];

    /** @return $this */
    public function set(string $key, bool $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    /** @return array<string> */
    public function all(): array
    {
        return array_keys(array_filter($this->options));
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        return new SqlQueryValue(implode(' ', $this->all()));
    }
}
