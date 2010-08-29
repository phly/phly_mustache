<?php

namespace PhlyTest\Mustache\TestAsset;

class ViewWithObjectForPartial
{
    public $name = 'Joe';
    public $winnings;
    public $value = 1000;
    public function taxed_value() 
    {
        return $this->value - ($this->value * 0.4);
    }
}
