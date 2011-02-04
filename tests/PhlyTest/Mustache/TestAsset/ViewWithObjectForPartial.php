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
 * View containing content used by a partial
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage UnitTests
 */
class PhlyTest_Mustache_TestAsset_ViewWithObjectForPartial
{
    public $name = 'Joe';
    public $winnings;
    public $value = 1000;
    public function taxed_value() 
    {
        return $this->value - ($this->value * 0.4);
    }
}
