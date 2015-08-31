<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Pragma;

use Phly\Mustache\Mustache;

/**
 * Pragma interface
 *
 * Pragmas may be used to extend the capabilities of Mustache during rendering.
 * Typically, this will involve parsing tokens slightly differently
 * (implicit-iterator allows specifying what "variable" token name can be
 * used), or allowing alternate subsitution strategies (for instance, allowing
 * scalar views, or replacing contents with a string representation of an
 * object).
 *
 * In most cases, you will want to use {@link Phly\Mustache\Pragma\PragmaNameAndTokensTrait},
 * as it provides some convenience features.
 */
interface PragmaInterface
{
    /**
     * Retrieve the name of the pragma
     *
     * @return string
     */
    public function getName();

    /**
     * Whether or not this pragma can handle the given token
     *
     * @param  int $token
     * @return bool
     */
    public function handlesToken($token);

    /**
     * Parse the provided token.
     *
     * If the pragma handles a given token, it is allowed to parse it; the
     * lexer will call this method when the token has been created, passing the
     * token struct.
     *
     * Token structs contain, minimally:
     *
     * - index 0: the token type (see the `Lexer::TOKEN_*` constants)
     * - index 1: the related data for the token
     *
     * The method MUST return a token struct on completion; if the pragma does
     * not need to do anything, it can simply `return $tokenStruct`.
     *
     * @param array $tokenStruct
     * @return array
     */
    public function parse(array $tokenStruct);

    /**
     * Render a given token.
     *
     * Returning an empty value returns control to the renderer.
     *
     * @param  int $token
     * @param  mixed $data
     * @param  mixed $view
     * @param  array $options
     * @param  Mustache $mustache Mustache instance handling rendering.
     * @return mixed
     */
    public function render($token, $data, $view, array $options, Mustache $mustache);
}
