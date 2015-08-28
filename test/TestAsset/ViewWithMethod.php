<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\TestAsset;

/**
 * View containing a method
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
