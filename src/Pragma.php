<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache;

/**
 * Pragma interface
 *
 * Pragmas may be used to extend the capabilities of the renderer. Typically,
 * this will involve parsing tokens slightly differently (implicit-iterator
 * allows specifying what "variable" token name can be used), or allowing
 * alternate subsitution strategies (for instance, allowing scalar views, or
 * replacing contents with a string representation of an object).
 *
 * In most cases, you will want to extend {@link Phly\Mustache\Pragma\AbstractPragma},
 * as it provides some convenience features.
 */
interface Pragma
{
    /**
     * Retrieve the name of the pragma
     *
     * @return string
     */
    public function getName();

    /**
     * Set the renderer instance
     *
     * @param  Renderer $renderer
     * @return void
     */
    public function setRenderer(Renderer $renderer);

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
     * @return mixed
     */
    public function handle($token, $data, $view, array $options);
}
