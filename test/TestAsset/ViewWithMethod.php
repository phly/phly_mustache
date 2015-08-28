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
 * View containing a method
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage UnitTests
 */
class ViewWithMethod
{
    public $name  = 'Chris';
    public $value = 1000000;
    public $in_ca = true;

    // @codingStandardsIgnoreStart
    public function taxed_value()
    {
        return $this->value - ($this->value * 0.4);
    }
    // @codingStandardsIgnoreEnd
}
