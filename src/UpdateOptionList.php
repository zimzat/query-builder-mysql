<?php

namespace Zimzat\QueryBuilder;

class UpdateOptionList extends OptionList
{
    public function lowPriority(bool $isSet = true): self
    {
        return $this->set('LOW_PRIORITY', $isSet);
    }

    public function ignore(bool $isSet = true): self
    {
        return $this->set('IGNORE', $isSet);
    }
}
