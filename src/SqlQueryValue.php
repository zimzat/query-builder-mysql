<?php

namespace Zimzat\QueryBuilder;

class SqlQueryValue
{
    private string $query;

    private array $values;

    public function __construct(string $query, array $values = [])
    {
        $this->query = $query;
        $this->values = $values;
    }

    /**
     * @return array{string,array<mixed>}
     */
    public function getQueryValues(): array
    {
        return [$this->query, $this->values];
    }
}
