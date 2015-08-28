<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\TestAsset;

/**
 * View intended to be included as part of a partial
 */
class PartialView
{
    public $value = 1000;

    // @codingStandardsIgnoreStart
    public function taxed_value()
    {
        return $this->value - ($this->value * 0.4);
    }
    // @codingStandardsIgnoreEnd
}
