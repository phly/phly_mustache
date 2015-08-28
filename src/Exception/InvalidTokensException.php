<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Exception;

use RuntimeException;

/**
 * Exception raised when we don't have tokens.
 */
class InvalidTokensException extends RuntimeException implements ExceptionInterface
{
}
