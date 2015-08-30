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
    protected $tokensHandled = [
        Lexer::TOKEN_VARIABLE,
        Lexer::TOKEN_VARIABLE_RAW,
    ];

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
    public function handle($token, $data, $view, array $options, Mustache $mustache)
    {
        // If we don't have a scalar view, implicit iteration isn't possible
        if (! is_scalar($view)) {
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
        if ($iterator !== $data) {
            return;
        }

        // Match found, so replace the value
        return ($escape)
            ? $mustache->getRenderer()->escape($view)
            : $view;
    }
}
