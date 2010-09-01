<?php

namespace PhlyTest\Mustache\TestAsset;

class ViewWithArrayEnumerable
{
    public $name = "Joe's shopping card";
    public $items = array(
        array('item' => 'bananas'),
        array('item' => 'apples'),
    );
}
