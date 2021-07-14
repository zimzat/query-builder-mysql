<?php

namespace Zimzat\QueryBuilder;

class InsertOptionList extends OptionList
{
    public function lowPriority(bool $isSet = true): self
    {
        return $this->set('LOW_PRIORITY', $isSet);
    }

    public function highPriority(bool $isSet = true): self
    {
        return $this->set('HIGH_PRIORITY', $isSet);
    }

    public function delayed(bool $isSet = true): self
    {
        return $this->set('DELAYED', $isSet);
    }
}
