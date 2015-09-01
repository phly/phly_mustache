<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Pragma;

use Phly\Mustache\Lexer;
use Phly\Mustache\Mustache;

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
 */
class ImplicitIterator implements PragmaInterface
{
    use PragmaNameAndTokensTrait;

    /**
     * Pragma name
     *
     * @var string
     */
    private $name = 'IMPLICIT-ITERATOR';

    /**
     * Tokens handled by this pragma
     * @var array
     */
    private $tokensHandled = [
        Lexer::TOKEN_VARIABLE,
        Lexer::TOKEN_VARIABLE_RAW,
    ];

    /**
     * Parse a given token and its data.
     *
     * In the case of the implicit iterator, nothing needs to be done.
     *
     * {@inheritDoc}
     */
    public function parse(array $tokenStruct)
    {
        return $tokenStruct;
    }

    /**
     * Render a given token.
     *
     * Attempts to render a token. If the view is non-scalar, the token is not
     * one it handles, or the variable does not match the iterator name, it
     * returns null, returning control to the renderer.
     *
     * Otherwise, it will output the view, escaping it unless the token
     * indicates a raw value.
     *
     * @param  array $tokenStruct
     * @param  mixed $view
     * @param  array $options
     * @return mixed
     */
    public function render(array $tokenStruct, $view, array $options, Mustache $mustache)
    {
        // If we don't have a scalar view, implicit iteration isn't possible
        if (! is_scalar($view)) {
            return;
        }

        // Do we escape?
        $escape = true;
        switch ($tokenStruct[0]) {
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
        if ($iterator !== $tokenStruct[1]) {
            return;
        }

        // Match found, so replace the value
        return ($escape)
            ? $mustache->getRenderer()->escape($view)
            : $view;
    }
}
