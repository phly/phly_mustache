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
     * Handle a given token
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
    public function handle($token, $data, $view, array $options, Mustache $mustache);
}
