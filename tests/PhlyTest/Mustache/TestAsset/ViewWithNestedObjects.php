<?php

namespace PhlyTest\Mustache\TestAsset;

class ViewWithNestedObjects
{
    public function __construct()
    {
        $this->a = new NestedObject();
    }
}
