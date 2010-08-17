<?php

namespace PhlyTest\Mustache\TestAsset;

class ViewWithObjectForPartial
{
    public $name = 'Joe';
    public $winnings;

    public function __construct()
    {
        $this->winnings = new PartialView();
    }
}
