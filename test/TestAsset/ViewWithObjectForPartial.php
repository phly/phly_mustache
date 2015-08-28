<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\TestAsset;

/**
 * View containing content used by a partial
 */
class ViewWithObjectForPartial
{
    public $name = 'Joe';
    public $winnings;
    public $value = 1000;

    // @codingStandardsIgnoreStart
    public function taxed_value()
    {
        return $this->value - ($this->value * 0.4);
    }
    // @codingStandardsIgnoreEnd
}
