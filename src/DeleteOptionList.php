<?php

namespace Zimzat\QueryBuilder;

class DeleteOptionList extends OptionList
{
    public function lowPriority(bool $isSet = true): self
    {
        return $this->set('LOW_PRIORITY', $isSet);
    }

    public function quick(bool $isSet = true): self
    {
        return $this->set('QUICK', $isSet);
    }

    public function ignore(bool $isSet = true): self
    {
        return $this->set('IGNORE', $isSet);
    }
}
