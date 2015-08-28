<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Exception;

use DomainException;

/**
 * Exception raised when a malformed variable name is encountered in a template by the lexer.
 */
class InvalidVariableNameException extends DomainException implements ExceptionInterface
{
}
