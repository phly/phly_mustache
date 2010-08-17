<?php

namespace PhlyTest\Mustache\TestAsset;

class ViewWithTraversableObject
{
    public $name = "Joe's shopping card";
    public function items()
    {
        return new ArrayObject(array(
            'bananas',
            'apples',
        ));
    }
}
