<?php

namespace PhlyTest\Mustache\TestAsset;

class ViewWithMethod
{
    public $name  = 'Chris';
    public $value = 1000000;
    public $in_ca = true;

    public function taxed_value()
    {
        return $this->value - ($this->value * 0.4);
    }
}
