<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Exception;

use Phly\Mustache\Exception;

/**
 * Exception raised when an invalid/unknown state is reached in the lexer
 */
class InvalidStateException extends \Exception implements Exception
{
}
