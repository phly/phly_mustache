<?php

namespace Phly\Mustache\Pragma;

use Phly\Mustache\Pragma,
    Phly\Mustache\Renderer,
    Phly\Mustache\Lexer;

abstract class AbstractPragma implements Pragma
{
    /**
     * @var string Pragma name
     */
    protected $name;

    /**
     * @var array Tokens this pragma handles
     */
    protected $tokensHandled = array();

    /** @var Renderer */
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
