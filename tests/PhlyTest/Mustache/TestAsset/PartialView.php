<?php

namespace PhlyTest\Mustache\TestAsset;

class PartialView
{
    public $value = 1000;
    public function taxed_value() 
    {
        return $this->value - ($this->value * 0.4);
    }
}
