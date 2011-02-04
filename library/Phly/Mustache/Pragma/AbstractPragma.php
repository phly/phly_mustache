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

/**
 * Abstract pragma implementation
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage Pragma
 */
abstract class Phly_Mustache_Pragma_AbstractPragma implements Phly_Mustache_Pragma
{
    /**
     * @var string Pragma name
     */
    protected $name;

    /**
     * @var array Tokens this pragma handles
     */
    protected $tokensHandled = array();

    /** @var Phly_Mustache_Renderer */
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
     * @param  Phly_Mustache_Renderer $renderer 
     * @return void
     */
    public function setRenderer(Phly_Mustache_Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Retrieve renderer
     * 
     * @return Phly_Mustache_Renderer
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
