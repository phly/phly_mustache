<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\TestAsset;

/**
 * View with a nested object
 */
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
        $this->list = [
            $item1,
            $item2,
        ];
    }
}
