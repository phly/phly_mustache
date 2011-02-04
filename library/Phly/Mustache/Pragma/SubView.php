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
 * Sub-view description
 *
 * Objects extending this may be used to describe sub-views that should be 
 * rendered as substitutions for template variables.
 * 
 * @category   Phly
 * @package    phly_mustache
 * @subpackage Pragma
 */
class Phly_Mustache_Pragma_SubView
{
    /** @var string */
    protected $template;

    /** @var null|array|object */
    protected $view;

    /**
     * Constructor
     *
     * @param  string $template 
     * @param  null|array|object $view 
     * @return void
     */
    public function __construct($template, $view = null)
    {
        if (!is_string($template)) {
            throw new Phly_Mustache_Exception_InvalidTemplateException();
        }
        if (null !== $view && !is_array($view) && !is_object($view)) {
            throw new InvalidArgumentException('View must be an array or object');
        }
        $this->template = $template;
        $this->view     = $view;
    }

    /**
     * Retrieve template
     * 
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Retrieve view
     * 
     * @return array|object
     */
    public function getView()
    {
        return $this->view;
    }
}

