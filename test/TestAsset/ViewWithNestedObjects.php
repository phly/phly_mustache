<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\TestAsset;

/**
 * View containing a nested object
 */
class ViewWithNestedObjects
{
    public function __construct()
    {
        $this->a = new NestedObject();
    }
}
