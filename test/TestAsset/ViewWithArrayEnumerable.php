<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\TestAsset;

/**
 * View with an embedded array enumerable for iteration
 */
class ViewWithArrayEnumerable
{
    public $name = "Joe's shopping card";
    public $items = [
        ['item' => 'bananas'],
        ['item' => 'apples'],
    ];
}
