<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Exception;

use InvalidArgumentException;

/**
 * Exception raised when an invalid template path is provided.
 */
class InvalidTemplatePathException extends InvalidArgumentException implements ExceptionInterface
{
}
