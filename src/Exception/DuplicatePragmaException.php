<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Exception;

use InvalidArgumentException;

/**
 * Exception raised when attempting to inject a pragma that is already present in the collection.
 */
class DuplicatePragmaException extends InvalidArgumentException implements ExceptionInterface
{
}
