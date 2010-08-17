<?php

namespace Phly\Mustache;

use ArrayObject,
    SplStack;

/**
 * Mustache implementation
 * 
 * @todo Allow specifying an alternate template suffix
 * @todo Prevent duplicate paths from being added
 * @license New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */
class Mustache
{
    /** @var array Cached file-based templates */
    protected $cachedTemplates = array();

    /** @var SplStack Stack of template paths to search */
    protected $templatePath;

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->templatePath = new SplStack;
    }

    /**
     * Add a template path to the template path stack
     * 
     * @param  string $path 
     * @return Mustache
     * @throws InvalidTemplatePathException
     */
    public function setTemplatePath($path)
    {
        if (!is_dir($path)) {
            throw new InvalidTemplatePathException();
        }
        $this->templatePath->push($path);
        return $this;
    }

    /**
     * Render a template using a view, and optionally a list of partials
     * 
     * @param  string $template Either a template string or a template file in the template path
     * @param  array|object $view An array or object with items to inject in the template
     * @param  array|object $partials A list of partial names/template pairs for rendering as partials
     * @return string
     * @throws InvalidPartialsException
     */
    public function render($template, $view, $partials = null)
    {
        if (!strstr($template, '{{')) {
            $template = $this->getTemplateFromFile($template);
        }

        if (null !== $partials) {
            if (!is_array($partials) && !is_object($partials)) {
                throw new InvalidPartialsException();
            }
            if (is_object($partials)) {
                if ($partials instanceof ArrayObject) {
                    $partials = $partials->getArrayCopy();
                } else {
                    $partials = (array) $partials;
                }
            }
        }

        /*
         * - Tokenize template
         * - Determine what pragmas are in place and update rules
         * - Handle straight variable substitutions
         *   - Escape double mustaches
         *   - Do not escape triple mustaches
         * - Render and capture partials
         * - Handle sections/loops/higher order sections
         */
    }

    /**
     * Locate and retrieve a template in the template path stack
     * 
     * @param  string $template 
     * @return string
     * @throws TemplateNotFoundException
     */
    protected function getTemplateFromFile($template)
    {
        if (array_key_exists($template, $this->cachedTemplates)) {
            return $this->cachedTemplates[$template];
        }

        foreach ($this->templatePath as $path) {
            $file = $path . DIRECTORY_SEPARATOR . $template . '.html';
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $this->cachedTemplates[$template] = $content;
                return $content;
            }
        }
        throw new TemplateNotFoundException('Template by name "' . $template . '" not found');
    }
}
