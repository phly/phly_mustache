<?php
/**
 * phly_mustache
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage Pragma
 * @copyright  Copyright (c) 2010 Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Pragma;

use Phly\Mustache\Lexer;

/**
 * IMPLICIT-ITERATOR pragma
 *
 * When enabled, allows passing an indexed array or Traversable object to a
 * section within a view, instead of an array of associative arrays or array of
 * objects. By default, the tag {{.}} will render the current item in the array;
 * you may specify an alternate tag name using the "iterator" option of the
 * pragma:
 *
 * <code>
 * {{%IMPLICIT-ITERATOR iterator=foo}}
 * {{#section}}
 *     {{foo}}
 * {{/section}}
 * </code>
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage Pragma
 */
class ImplicitIterator extends AbstractPragma
{
    /**
     * Pragma name
     * @var string
     */
    protected $name = 'IMPLICIT-ITERATOR';

    /**
     * Tokens handled by this pragma
     * @var array
     */
    protected $tokensHandled = array(
        Lexer::TOKEN_VARIABLE,
        Lexer::TOKEN_VARIABLE_RAW,
    );

    /**
     * Handle a given token
     *
     * Returning an empty value returns control to the renderer.
     *
     * @param  int $token
     * @param  mixed $data
     * @param  mixed $view
     * @param  array $options
     * @return mixed
     */
    public function handle($token, $data, $view, array $options)
    {
        // If we don't have a scalar view, implicit iteration isn't possible
        if (!is_scalar($view)) {
            return;
        }

        // Do we escape?
        $escape = true;
        switch ($token) {
            case Lexer::TOKEN_VARIABLE:
                // Yes
                break;
            case Lexer::TOKEN_VARIABLE_RAW:
                // No
                $escape = false;
                break;
            default:
                // Wrong token type! Just return;
                return;
        }

        // Get the iterator option, and compare it to the token we received
        $iterator = isset($options['iterator']) ? $options['iterator'] : '.';
        if ($iterator === $data) {
            // Match found, so replace the value
            return ($escape) ? $this->getRenderer()->escape($view) : $view;
        }
    }
}
