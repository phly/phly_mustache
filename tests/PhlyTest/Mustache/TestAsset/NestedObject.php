<?php

namespace PhlyTest\Mustache\TestAsset;

class NestedObject
{
    public $title = 'this is an object';
    public $description = 'one of its attributes is a list';

    public function __construct()
    {
        $item1 = new \stdClass;
        $item1->label = 'listitem1';
        $item2 = new \stdClass;
        $item2->label = 'listitem2';
        $this->list = array(
            $item1,
            $item2,
        );
    }
}
