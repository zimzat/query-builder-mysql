<?php

namespace Zimzat\QueryBuilder;

class SelectOptionList extends OptionList
{
    public function distinct(bool $isSet = true): self
    {
        return $this->set('DISTINCT', $isSet);
    }

    public function highPriority(bool $isSet = true): self
    {
        return $this->set('HIGH_PRIORITY', $isSet);
    }

    public function straightJoin(bool $isSet = true): self
    {
        return $this->set('STRAIGHT_JOIN', $isSet);
    }

    public function smallResult(bool $isSet = true): self
    {
        return $this->set('SQL_SMALL_RESULT', $isSet);
    }

    public function bigResult(bool $isSet = true): self
    {
        return $this->set('SQL_BIG_RESULT', $isSet);
    }

    public function bufferResult(bool $isSet = true): self
    {
        return $this->set('SQL_BUFFER_RESULT', $isSet);
    }

    public function noCache(bool $isSet = true): self
    {
        return $this->set('SQL_NO_CACHE', $isSet);
    }

    public function calcFoundRows(bool $isSet = true): self
    {
        return $this->set('SQL_CALC_FOUND_ROWS', $isSet);
    }
}
