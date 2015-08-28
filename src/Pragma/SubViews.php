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

/**
 * SUB-VIEWS pragma
 *
 * When enabled, allows passing "sub-views". A sub view is an object
 * implementing SubView, which contains the following methods:
 * - getTemplate()
 * - getView()
 * When detected as the value of a variable, the pragma will render the given
 * template using the view provided, and return that value as the value of the
 * variable.
 *
 * Consider the following template:
 * <code>
 * {{%SUB-VIEWS}}
 * <html>
 * <head>
 * {{>header}}
 * </head>
 * <body>
 *     {{content}}
 * </body>
 * </html>
 * </code>
 *
 * And the following partials:
 * <code>
 * {{!header}}
 *     <title>{{title}}</title>
 *
 * {{!controller/action}}
 * {{greeting}}, {{name}}!
 * </code>
 *
 *
 * Along with the following view:
 * <code>
 * $content = new SubView('controller/action', array(
 *     'name'     => 'Matthew',
 *     'greeting' => 'Welcome',
 * ));
 * $view = array(
 *     'title'   => 'Greeting Page',
 *     'content' => $content,
 * );
 * </code>
 *
 * Rendered, this would now be:
 * <code>
 * <html>
 * <head>
 *     <title>Greeting Page</title>
 * </head>
 * <body>
 *     Welcome, Matthew!
 * </body>
 * </html>
 * </code>
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage Pragma
 */
class SubViews extends AbstractPragma
{
    /**
     * Name of this pragma
     * @var string
     */
    protected $name = 'SUB-VIEWS';

    /**
     * Tokens this pragma handles
     * @var array
     */
    protected $tokensHandled = array(
        Lexer::TOKEN_VARIABLE,
    );

    /**
     * Mustache manager
     * @var Mustache
     */
    protected $manager;

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
    public function handle($token, $data, $view, array $options)
    {
        $subView = $this->getValue($data, $view);

        // If the view value is not a SubView, we can't handle it here
        if (!$subView instanceof SubView) {
            return;
        }

        // If we don't have a manager instance, can't do anything with it
        if (null === ($manager = $this->getManager())) {
            return;
        }

        // Get template
        $template = $subView->getTemplate();

        // Get sub view; use current view if none found
        $localView  = $subView->getView();
        if (null === $localView) {
            $localView = $view;
        }

        // Render sub view and return it
        return $manager->render($template, $localView);
    }

    /**
     * Get the value represented by the key $data from the $view
     *
     * Returns boolean false if unable to retrieve the value.
     *
     * @param  string $data
     * @param  mixed $view
     * @return false|mixed
     */
    protected function getValue($data, $view)
    {
        if (is_scalar($view)) {
            return false;
        }

        if (is_array($view) || $view instanceof ArrayAccess) {
            if (!isset($view[$data])) {
                return false;
            }
            return $view[$data];
        }

        if (is_object($view)) {
            if (!isset($view->{$data})) {
                return false;
            }
            return $view->{$data};
        }
    }
}
