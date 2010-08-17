<?php

namespace PhlyTest\Mustache\TestAsset;

class ViewWithHigherOrderSection
{
    public $name = 'Tater';

    public function bolder()
    {
        return function($text, $renderer) {
            return '<b>' . call_user_func($renderer, $text) . '</b>';
        };
    }
}
