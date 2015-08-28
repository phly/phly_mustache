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

namespace Phly\Mustache\Exception;

use Phly\Mustache\Exception;

/**
 * Exception raised when an invalid callback is registered for escaping
 * variables
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage Exception
 */
class InvalidEscaperException extends \Exception implements Exception
{
}
