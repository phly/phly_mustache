<?php
/**
 * phly_mustache
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage Exception
 * @copyright  Copyright (c) 2010 Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/** @namespace */
namespace Phly\Mustache\Exception;

use Phly\Mustache\Exception;

/**
 * Exception raised when the template provided to the lexer is not a string
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage Exception
 */
class InvalidTemplateException 
    extends \Exception 
    implements Exception
{
}
