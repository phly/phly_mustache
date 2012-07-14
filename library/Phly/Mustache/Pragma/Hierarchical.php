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

use Phly\Mustache\Mustache;
use Phly\Mustache\Lexer;

class Hierarchical extends AbstractPragma
{
    /**
     * Name of this pragma
     * @var string
     */
    protected $name = 'HIERARCHICAL';

    /** 
     * Mustache manager
     * @var Mustache
     */
    protected $manager;

    /**
     * Tokens this pragma handles
     * @var array
     */
    protected $tokensHandled = array(
        Lexer::TOKEN_VARIABLE,
    );

    /**
     * Constructor
     * 
     * @param  Mustache $manager 
     * @return void
     */
    public function __construct(Mustache $manager = null)
    {
        if (null !== $manager) {
            $this->setManager($manager);
        }
    }

    /**
     * Set manager object
     *
     * Sets manager object and registers self as a pragma on the renderer.
     * 
     * @param  Mustache $manager 
     * @return SubViews
     */
    public function setManager(Mustache $manager)
    {
        $this->manager = $manager;
        $this->manager->getRenderer()->addPragma($this);
        return $this;
    }

    /**
     * Retrieve manager object
     * 
     * @return Mustache
     */
    public function getManager()
    {
        return $this->manager;
    }

    public function handle($token, $data, $view, array $options)
    {
    }
}
