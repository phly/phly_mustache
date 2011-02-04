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

/**
 * View with a nested object
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage UnitTests
 */
class PhlyTest_Mustache_TestAsset_NestedObject
{
    public $title = 'this is an object';
    public $description = 'one of its attributes is a list';

    public function __construct()
    {
        $item1 = new stdClass;
        $item1->label = 'listitem1';
        $item2 = new stdClass;
        $item2->label = 'listitem2';
        $this->list = array(
            $item1,
            $item2,
        );
    }
}
