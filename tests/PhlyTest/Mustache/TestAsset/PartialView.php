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
 * View intended to be included as part of a partial
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage UnitTests
 */
class PartialView
{
    public $value = 1000;
    public function taxed_value() 
    {
        return $this->value - ($this->value * 0.4);
    }
}
