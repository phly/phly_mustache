<?php
/**
 * phly_mustache
 *
 * @category   PhlyTest
 * @package    phly_mustache
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2010 Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/** @namespace */
namespace PhlyTest\Mustache\TestAsset;

/**
 * View containing a traversable object
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage UnitTests
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
