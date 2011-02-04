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

/**
 * Exception raised when the template provided to the lexer is not a string
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage Exception
 */
class Phly_Mustache_Exception_InvalidTemplateException 
    extends Exception 
    implements Phly_Mustache_Exception
{
}
