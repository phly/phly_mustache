<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\TestAsset;

/**
 * View containing a traversable object
 */
class ViewWithTraversableObject
{
    public $name = "Joe's shopping card";
    public $items;

    public function __construct()
    {
        $this->items = new \ArrayObject([
            ['item' => 'bananas'],
            ['item' => 'apples'],
        ]);
    }
}
