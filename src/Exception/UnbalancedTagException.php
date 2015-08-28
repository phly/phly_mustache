<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Exception;

use DomainException;

/**
 * Exception raised when an unclosed tag is encountered by the lexer.
 */
class UnbalancedTagException extends DomainException implements ExceptionInterface
{
}
