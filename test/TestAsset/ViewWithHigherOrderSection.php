<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\TestAsset;

/**
 * View containing a "higher order" section
 */
class ViewWithHigherOrderSection
{
    public $name = 'Tater';

    public function bolder()
    {
        return function ($text, $renderer) {
            return '<b>' . call_user_func($renderer, $text) . '</b>';
        };
    }
}
