<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Pragma;

use Phly\Mustache\Pragma;
use Phly\Mustache\Renderer;
use Phly\Mustache\Lexer;

/**
 * Abstract pragma implementation
 */
abstract class AbstractPragma implements Pragma
{
    /**
     * Pragma name
     * @var string
     */
    protected $name;

    /**
     * Tokens this pragma handles
     * @var array
     */
    protected $tokensHandled = [];

    /**
     * Renderer
     * @var Renderer
     */
    protected $renderer;

    /**
     * Retrieve the name of the pragma
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the renderer instance
     *
     * @param  Renderer $renderer
     * @return void
     */
    public function setRenderer(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Retrieve renderer
     *
     * @return Renderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Whether or not this pragma can handle the given token
     *
     * @param  int $token
     * @return bool
     */
    public function handlesToken($token)
    {
        return in_array($token, $this->tokensHandled);
    }
}
