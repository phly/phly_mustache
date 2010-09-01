<?php

namespace PhlyTest\Mustache\TestAsset;

class ViewWithTraversableObject
{
    public $name = "Joe's shopping card";
    public $items;

    public function __construct()
    {
        $this->items = new \ArrayObject(array(
            array('item' => 'bananas'),
            array('item' => 'apples'),
        ));
    }
}
